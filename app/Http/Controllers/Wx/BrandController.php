<?php

namespace App\Http\Controllers\Wx;

use App\Services\Goods\BrandServices;
use App\Utils\CodeResponse;
use Illuminate\Http\Request;

class BrandController extends WxController
{
    protected $only = [];

    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');
        $list = BrandServices::getInstance()->getBrandList($page, $limit, $sort, $order);
        return $this->successPaginate($list);

    }


    public function detail(Request $request)
    {
        $id    = $this->verifyId('id');
        $brand = BrandServices::getInstance()->getBrand($id);

        if (is_null($brand)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY);
        }
        return $this->success($brand);
    }


}
