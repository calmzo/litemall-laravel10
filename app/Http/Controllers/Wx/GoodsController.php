<?php

namespace App\Http\Controllers\Wx;

use App\Exceptions\BusinessException;
use App\Inputs\GoodsListInput;
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\CategoryServices;
use App\Services\Goods\GoodsAttributeServices;
use App\Services\Goods\GoodsProductServices;
use App\Services\Goods\GoodsServices;
use App\Services\Goods\GoodsSpecificationServices;
use App\Services\Goods\IssueServices;
use App\Services\SearchHistoryServices;
use App\Utils\CodeResponse;
use App\Utils\Constant;
use Illuminate\Http\Request;

class GoodsController extends WxController
{

    protected $only = [];

    public function count()
    {
        $goodsCount = GoodsServices::getInstance()->countGoodsOnSales();
        return $this->success($goodsCount);

    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws BusinessException
     * 获取商品分类的数据
     */
    public function category(Request $request)
    {
        $id = $this->verifyId('id');
        $currentCategory = CatalogServices::getInstance()->findById($id);
        if (is_null($currentCategory)) {
            throw new BusinessException(CodeResponse::SYSTEM_ERROR);
        }
        $parent = null;
        if ($currentCategory->pid == 0) {
            $parentCategory = $currentCategory;
            $brotherCategory = CatalogServices::getInstance()->getL2List($currentCategory->id);
            $currentCategory = !is_null($brotherCategory) ? $brotherCategory->first() : $currentCategory;
        } else {
            $parentCategory = CatalogServices::getInstance()->findById($currentCategory->pid);
            $brotherCategory = CatalogServices::getInstance()->getL2List($currentCategory->pid);
        }

        return $this->success(compact('currentCategory', 'parentCategory', 'brotherCategory'));


    }

    public function list()
    {
        $input = GoodsListInput::new();
        //添加到搜索历史
        if ($this->isLogin() && !empty($input->keyWord)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $input->keyword,
                Constant::SEARCH_HISTORY_FROM_WX);
        }

        //todo 优化查询传参 查询列表数据
        $goodsList = GoodsServices::getInstance()->goodsLists($input);

        //查询商品所属类目列表
        $categoryIds = GoodsServices::getInstance()->getCatIds($input);

//        $caregoryList = GoodsServices::getInstance()->listL2Gategory($input);
        $categoryList = CategoryServices::getInstance()->getCategoryByIds($categoryIds);

        $lists                       = $this->paginate($goodsList);
        $lists['filterCategoryList'] = $categoryList;
        return $this->success($lists);
    }

    public function detail(Request $request)
    {
        $id = $this->verifyId('id');
        //商品信息
        $info = GoodsServices::getInstance()->findById($id);
        //商品属性
        $goodsAttributeList = GoodsServices::getInstance()->getGoodsAttributesList($id);
        //商品规格 返回的是定制的GoodsSpecificationVo
        $specificationList = GoodsServices::getInstance()->getGoodsSpecification($id);

        //商品规格对应的数量和价格
        $productList = GoodsServices::getInstance()->getGoodsProducts($id);

        //商品问题，这里是一些通用问题
        $issue = GoodsServices::getInstance()->getGoodsIssue();

        //商品品牌商
        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : (object) [];

        //商品评论
        $goodComment = CommentServices::getInstance()->getGoodsCommentWithUserInfo($id);

        //用户收藏数
        $userHasCollect = CollectServices::getInstance()->getGoodsCollect($id);

        if ($this->isLogin()) {
            // 记录用户的足迹 异步处理 todo
            GoodsServices::getInstance()->saveFootprint($this->userId(), $id);

        }

        //团购信息 todo
        $groupon = [];


        $data = [
            'info' => $info,
            'userHasCollect' => $userHasCollect,
            'issue' => $issue,
            'attribute' => $goodsAttributeList,
            'specificationList' => $specificationList,
            'comment' => $goodComment,
            'productList' => $productList,
            'brand' => $brand,
            'groupon' => $groupon,
            'shareImage' => $info->share_url,
            'share' => false

        ];
        return $this->success($data);
    }

}
