<?php

namespace App\Http\Controllers\Wx;

use App\Exceptions\BusinessException;
use App\Inputs\GoodsListInput;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
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
        $goodsCount = GoodsServices::getInstance()->queryOnSale();
        return $this->success($goodsCount);

    }

    /**
     * 商品分类类目
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws BusinessException
     */
    public function category(Request $request)
    {
        $id = $this->verifyId('id');
        $cur = CatalogServices::getInstance()->findById($id);
        if (is_null($cur)) {
            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $parent = null;
        $children = null;
        if ($cur->pid == 0) {
            $parent = $cur;
            $children = CatalogServices::getInstance()->getL2List($cur->id);
            $cur = $children->first() ?? $cur;
        } else {
            $parent = CatalogServices::getInstance()->findById($cur->pid);
            $children = CatalogServices::getInstance()->getL2List($cur->pid);
        }


        return $this->success([
            'currentCategory' => $cur,
            'parentCategory' => $parent,
            'brotherCategory' => $children,

        ]);


    }

    public function list()
    {
        $input = GoodsListInput::new();
        //添加到搜索历史
        if ($this->isLogin() && !empty($input->keyWord)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $keyword, Constant::SEARCH_HISTORY_FROM_WX);
        }

        //todo 优化查询传参
        $goodsList = GoodsServices::getInstance()->listGoods($input);

        $caregoryList = GoodsServices::getInstance()->listL2Gategory($input);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $caregoryList;
        return $this->success($goodsList);
    }

    public function detail(Request $request)
    {
        $id = $this->verifyId('id');
        //商品信息
        $info = GoodsServices::getInstance()->findById($id);
        //商品属性
        $goodsAttributeList = GoodsAttributeServices::getInstance()->queryListByGid($id);
        //商品规格 返回的是定制的GoodsSpecificationVo
        $specificationList = GoodsSpecificationServices::getInstance()->getSpecificationVoList($id);

        //商品规格对应的数量和价格
        $productList = GoodsProductServices::getInstance()->queryListByGid($id);

        //商品问题，这里是一些通用问题
        $issue = IssueServices::getInstance()->querySelective("", 1, 4);

        //商品品牌商
        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : (object) [];

        // 用户收藏
        $userHasCollect = 0;
        if ($this->isLogin()) {
            $userHasCollect = GoodsServices::getInstance()->getCollectCount($this->userId(), 0, $id);
            // 记录用户的足迹 异步处理 todo
            GoodsServices::getInstance()->saveFootprint($this->userId(), $id);

        }


        //团购信息 todo
        $groupon = [];

        //评论
        $comments = GoodsServices::getInstance()->getCommentWithUserInfo($id);


        $data = [
            'info' => $info,
            'userHasCollect' => $userHasCollect,
            'issue' => $issue,
            'attribute' => $goodsAttributeList,
            'specificationList' => $specificationList,
            'comment' => $comments,
            'productList' => $productList,
            'brand' => $brand,
            'groupon' => $groupon,
            'shareImage' => $info->share_url,
            'share' => false

        ];
        return $this->success($data);
    }

}
