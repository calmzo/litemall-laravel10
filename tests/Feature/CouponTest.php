<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\Promotion\CouponServices;
use App\Utils\CodeResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CouponTest extends TestCase
{
    public function testList(): void
    {
        $this->assertLitemallApiGet('wx/coupon/list');
    }
    public function testMylist(): void
    {
        $this->assertLitemallApiGet('wx/coupon/mylist');
    }
//    public function testReceiveLimit(): void
//    {
//
//        $this->expectExceptionObject(new BusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过'));
//        CouponServices::getInstance()->receive(1, 1);
//
//    }

//    public function testReceive(): void
//    {
//
//        $id = Coupon::query()->insertGetId([
//            'name' => '活动优惠券',
//            'desc' => '活动优惠券',
//            'tag' => '满50减20',
//            'total' => 0,
//            'discount' => 20,
//            'min' => 50,
//            'limit' => 1,
//            'time_type' => 0,
//            'days' => 1
//        ]);
//        $ret = CouponServices::getInstance()->receive(1, $id);
////        $this->assertTrue($ret);
//        $ret = CouponUser::query()->where('user_id', 1)->where('coupon_id', $id)->first();
//        $this->assertNotEmpty($ret);
//
//
//    }
}
