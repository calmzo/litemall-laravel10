<?php

namespace App\Utils;

class CodeResponse
{
    //通用
    const SUCCESS = [0, '成功'];
    const FAIL = [-1, '错误'];


    const BADARGUMENT = [401, '参数不对'];
    const PARAM_VALUE_ILLEGAL = [402, '参数值不对'];


    const UN_LOGIN = [501, '请登录'];
    const UPDATED_FAIL = [505, '更新数据失败'];


    //业务返回码
    const AUTH_INVALID_ACCOUNT = [700, '账号不存在'];
    const AUTH_CAPTCHA_UNSUPPORT = [701, ''];
    const AUTH_CAPTCHA_FREQUENCY = [702, '验证码未超时1分钟，不能发送'];
    const AUTH_CAPTCHA_UNMATCH = [703, ''];
    const AUTH_NAME_REGISTERED = [704, ''];
    const AUTH_MOBILE_REGISTERED = [705, ''];
    const AUTH_MOBILE_UNREGISTERED = [706, ''];
    const AUTH_INVALID_MOBILE = [707, ''];
    const AUTH_OPENID_UNACCESS = [708, ''];
    const AUTH_OPENID_BINDED = [709, ''];

    const GOODS_UNSHELVE = [710, ''];
    const GOODS_NO_STOCK = [711, ''];
    const GOODS_UNKNOWN = [712, ''];
    const GOODS_INVALID = [713, ''];

    const ORDER_UNKNOWN = [720, ''];
    const ORDER_INVALID = [721, ''];
    const ORDER_CHECKOUT_FAIL = [722, ''];
    const ORDER_CANCEL_FAIL = [723, ''];
    const ORDER_PAY_FAIL = [724, ''];
    // 订单当前状态下不支持用户的操作，例如商品未发货状态用户执行确认收货是不可能的。
    const ORDER_INVALID_OPERATION = [725, ''];
    const ORDER_COMMENTED = [726, ''];
    const ORDER_COMMENT_EXPIRED = [727, ''];

    const GROUPON_EXPIRED = [730, ''];
    const GROUPON_OFFLINE = [731, ''];
    const GROUPON_FULL = [732, ''];
    const GROUPON_JOIN = [733, ''];

    const COUPON_EXCEED_LIMIT = [740, ''];
    const COUPON_RECEIVE_FAIL= [741, ''];
    const COUPON_CODE_INVALID= [742, ''];

    const AFTERSALE_UNALLOWED = [750, ''];
    const AFTERSALE_INVALID_AMOUNT = [751, ''];
    const AFTERSALE_INVALID_STATUS = [752, ''];

}
