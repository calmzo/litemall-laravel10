<?php

namespace App\Services\Order;

use App\Services\BaseServices;

class OrderServices extends BaseServices
{

    public function h5payOrder()
    {
        return [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject'      => 'test subject-测试订单',
        ];
    }




}

