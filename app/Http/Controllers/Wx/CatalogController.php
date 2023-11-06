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
        $l1CatList = CatalogServices::getInstance()->getL1List();

        // 当前一级分类目录
        if (empty($id)) {
            $currentCategory = $l1CatList[0];
        } else {
            $currentCategory = CatalogServices::getInstance()->findById($id);
        }

        $currentSubCategory = null;
        // 当前一级分类目录对应的二级分类目录
        if (!is_null($currentCategory)) {
            $currentSubCategory = CatalogServices::getInstance()->getL2List($currentCategory->id);
        }

        return $this->success([
            'currentCategory' => $currentCategory,
            'categoryList' => $l1CatList,
            'currentSubCategory' => $currentSubCategory,

        ]);
    }


    public function current(Request $request)
    {
        // 所有一级分类目录
        $id = $request->input('id');
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        // 当前分类
        $currentCategory = Category::query()->find($id);
        if (is_null($currentCategory)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $currentSubCategory = CatalogServices::getInstance()->getL2List($currentCategory->id);

        return $this->success([
            'currentCategory' => $currentCategory,
            'currentSubCategory' => $currentSubCategory,

        ]);
    }


}
