<?php

namespace App\Http\Controllers\Wx;

use App\Inputs\PageInput;
use App\Services\Goods\BrandServices;
use App\Utils\CodeResponse;
use Illuminate\Http\Request;

class BrandController extends WxController
{
    protected $only = [];

    public function list(Request $request)
    {
        $page = PageInput::new();
        $list = BrandServices::getInstance()->getBrandList($page,
            ['id', 'name', 'desc', 'pic_url', 'floor_price']);
        return $this->successPaginate($list);

    }


    public function detail(Request $request)
    {
        $id = $this->verifyId('id');
        $brand = BrandServices::getInstance()->getBrand($id);

        if (is_null($brand)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY, '数据不存在');
        }
        return $this->success($brand);
    }


}
