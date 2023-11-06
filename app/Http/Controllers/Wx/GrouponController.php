<?php

namespace App\Http\Controllers\Wx;

use App\Services\Promotion\GrouponServices;

class GrouponController extends WxController
{

    protected $only = [];

    public function createGrouponShareImage()
    {
        $resp = GrouponServices::getInstance()->createGrouponShareImage();
        return $resp;
//        return response()->make($resp)->header('Content-Type', 'image/png');
    }

}
