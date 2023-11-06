<?php

namespace App\Http\Controllers\Wx;
use App\Services\Order\OrderServices;
use Yansongda\LaravelPay\Facades\Pay;
class OrderController extends WxController
{
    protected $only = [];

    public function h5pay()
    {
        $order = OrderServices::getInstance()->h5payOrder();
        return Pay::alipay()->wap($order);
    }




}
