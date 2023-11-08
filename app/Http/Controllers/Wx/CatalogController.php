<?php

namespace App\Http\Controllers\Wx;

use App\Models\Goods\Category;
use App\Services\Goods\CatalogServices;
use App\Utils\CodeResponse;
use Illuminate\Http\Request;

class CatalogController extends WxController
{
    protected $only = [];

    public function index(Request $request)
    {
        // 所有一级分类目录
        $id = $request->input('id');
        $categoryList = CatalogServices::getInstance()->getL1List();

        // 当前一级分类目录
        if (empty($id)) {
            $currentCategory = $categoryList->first();
        } else {
            $currentCategory = $categoryList->where('id', $id)->first();
        }

        $currentSubCategory = null;
        // 当前一级分类目录对应的二级分类目录
        if (!is_null($currentCategory)) {
            $currentSubCategory = CatalogServices::getInstance()->getL2ListDataByPid($currentCategory->id);
        }

        return $this->success(compact('currentCategory', 'categoryList', 'currentSubCategory'));
    }


    public function current(Request $request)
    {
        // 所有一级分类目录
        $id = $request->input('id');
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        } else {
            $categoryLists   = CatalogServices::getInstance()->getL1List();
            $currentCategory = $categoryLists->where('id', $id)->first();
        }

        // 当前分类
        $currentCategory = Category::query()->find($id);
        if (is_null($currentCategory)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        }
//        $currentSubCategory = CatalogServices::getInstance()->getL2List($currentCategory->id);
        $currentSubCategory = CatalogServices::getInstance()->getL2ListDataByPid($currentCategory->id);

        return $this->success(compact('currentCategory', 'currentSubCategory'));
    }


}
