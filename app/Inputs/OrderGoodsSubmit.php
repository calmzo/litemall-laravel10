<?php


namespace App\Inputs;


class OrderGoodsSubmit extends Input
{
    public $cartId;
    public $addressId;
    public $couponId;
    public $userCouponId;
    public $message;
    public $grouponRulesId;
    public $grouponLinkId;


    public function rule()
    {
        return [
            'cartId'         => 'integer',
            'addressId'      => 'integer',
            'couponId'       => 'integer',
            'userCouponId'   => 'integer',
            'message'        => 'string',
            'grouponRulesId' => 'integer',
            'grouponLinkId'  => 'integer',
        ];
    }

}
