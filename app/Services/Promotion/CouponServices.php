<?php

namespace App\Services\Promotion;

use App\Models\Promotion\Coupon;
use App\Services\BaseServices;

class CouponServices extends BaseServices
{
    public function getCouponListByLimit($offset = 0, $limit = 3, $order = 'desc', $sort = 'add_time')
    {
        return Coupon::query()->offset($offset)->limit($limit)->orderBy($sort, $order)->get();
    }

}
