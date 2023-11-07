<?php

namespace App\Services\Promotion;

use App\CodeResponse;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\BaseServices;
use App\Utils\Constant;

class CouponServices extends BaseServices
{
    public function getCouponListByLimit($offset = 0, $limit = 3, $order = 'desc', $sort = 'add_time')
    {
        return Coupon::query()->offset($offset)->limit($limit)->orderBy($sort, $order)->get();
    }

    /**
     * @param $userId
     * @param  int  $offset
     * @param  int  $limit
     * @return Coupon[]|array|BuildsQueries[]|Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     * 获取登录用户没有领过的优惠券
     */
    public function getAvailableList($userId, $offset = 0, $limit = 3)
    {
        $couponIds = CouponUser::query()->whereUserId($userId)->get()->pluck('coupon_id')->toArray();
        return Coupon::query()->when(!empty($couponIds), function (Builder $builder) use ($couponIds) {
            return $builder->whereNotIn('id', $couponIds);
        })->offset($offset)->limit($limit)->get();
    }

    /**
     * @param  Coupon  $coupon
     * @param  CouponUser  $couponUser
     * @param $price
     * @return bool
     * 检查优惠券的有效性
     */
    public function checkCouponAndPrice(Coupon $coupon, CouponUser $couponUser, $price)
    {
        if (empty($couponUser) || empty($coupon)) {
            return false;
        }

        if ($coupon->id != $couponUser->coupon_id) {
            return false;
        }

        if ($coupon->status != Constant::COUPON_STATUS_NORMAL) {
            return false;
        }

        if (bccomp($coupon->min, $price) == 1) {
            return false;
        }

        $now = now();
        switch ($coupon->time_type) {
            case Constant::COUPON_TIME_TYPE_TIME:
                $start_time = strtotime($coupon->start_time);
                $end_time   = strtotime($coupon->end_time);
                if (!($start_time < time() && $end_time > time())) {
                    return false;
                }
                break;
            case Constant::COUPON_TIME_TYPE_DAYS:
                $expired = Carbon::parse($couponUser->add_time)->addDays($coupon->days);
                if ($now->isAfter($expired)) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * @param $userId
     * @return CouponUser[]|Builder[]|Collection
     * 获取用户可用的优惠券
     */
    public function getUsableCoupons($userId)
    {
        return CouponUser::query()->where('user_id', $userId)->where('status',
            Constant::COUPON_USER_STATUS_USABLE)->get();
    }

    /**
     * @param $userId
     * @param $checkedGoodsPrice
     * @param $couponId
     * @param $userCouponId
     * @return array
     * 获取合适的优惠券
     * @throws Exception
     */
    public function getUserMeetCoupons($userId, $checkedGoodsPrice, $couponId, $userCouponId)
    {
        $couponsUsers = CouponServices::getInstance()->getUsableCoupons($userId);
        $couponIds    = $couponsUsers->pluck('coupon_id')->toArray();
        $coupons      = CouponServices::getInstance()->getCouponsByIds($couponIds)->keyBy('id');
        $couponsUsers = $couponsUsers->filter(function (CouponUser $couponUser) use ($coupons, $checkedGoodsPrice) {
            $coupon = $coupons->get($couponUser->coupon_id);
            return CouponServices::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
        })->sortByDesc(function (CouponUser $couponUser) use ($coupons) {
            /** @var Coupon $coupon */
            $coupon = $coupons->get($couponUser->coupon_id);
            return $coupon->discount;
        });

        // 这里存在三种情况
        // 1. 用户不想使用优惠券，则不处理
        // 2. 用户想自动使用优惠券，则选择合适优惠券
        // 3. 用户已选择优惠券，则测试优惠券是否合适
        $couponPrice = 0;
        if (is_null($couponId) || $couponId == -1) {
            $userCouponId = -1;
            $couponId     = -1;
        } elseif ($couponId == 0) {
            /** @var CouponUser $couponUser */
            $couponUser   = $couponsUsers->first();
            $couponId     = $couponUser->coupon_id ?? 0;
            $userCouponId = $couponUser->id ?? 0;
            $couponPrice  = CouponServices::getInstance()->getCoupon($couponId)->discount ?? 0;
        } else {
            $coupon     = CouponServices::getInstance()->getCoupon($couponId);
            $couponUser = CouponServices::getInstance()->getCouponUser($userCouponId);
            $isValid    = CouponServices::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
            if ($isValid) {
                $couponPrice = $coupon->discount ?? 0;
            }
        }
        return [$couponId, $userCouponId, $couponPrice, $couponsUsers->count() ?? 0];
    }

    /**
     * @param $couponId
     * @param $userId
     * @throws BusinessException
     * 用户领取消费券
     */
    public function receive($couponId, $userId)
    {
        $coupon = $this->getCoupon($couponId);

        if (is_null($coupon)) {
            $this->throwBusinessException(CodeResponse::SYSTEM_ERROR);
        }

        //当前已领取的数量和总数量比较
        $total        = $coupon->total;
        $totalCoupons = $this->getCouponUserTotalByCouponId($couponId);
        if ($total != 0 && $totalCoupons >= $total) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT);
        }

        //当前用户已领取数量和用户限领取数量进行比较
        $limit           = $coupon->limit;
        $userCouponCount = $this->getUserCouponsCount($couponId, $userId);
        if ($limit != 0 && $userCouponCount >= $limit) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过');
        }

        //优惠券分发类型，比如注册赠券类型的优惠券不能领取
        $type = $coupon->type;
        if ($type == Constant::COUPON_TYPE_REGISTER) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '新用户优惠券自动发送');
        } elseif ($type == Constant::COUPON_TYPE_CODE) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券只能兑换');
        } elseif ($type != Constant::COUPON_TYPE_COMMON) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券类型不支持');
        }

        //优惠券的状态，已经过期或者下架
        $status = $coupon->status;
        if ($status == Constant::COUPON_STATUS_EXPIRED) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经过期');
        } elseif ($status == Constant::COUPON_STATUS_OUT) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已领完');
        }

        //用户领券记录
        $timeType = $coupon->time_type;
        if ($timeType == Constant::COUPON_TIME_TYPE_TIME) {
            $start_time = $coupon->start_time;
            $end_time   = $coupon->end_time;
        } elseif ($timeType == Constant::COUPON_TIME_TYPE_DAYS) {
            $start_time = \Illuminate\Support\Carbon::now()->toDateTimeString();
            $end_time   = date("Y-m-d H:i:s", time() + $coupon->days * 24 * 3600);
        }
        $this->saveCouponUser($userId, $couponId, $start_time, $end_time);
    }

    /**
     * @param $userId
     * @param $couponId
     * @param $start_time
     * @param $end_time
     * 记录用户领券
     */
    public function saveCouponUser($userId, $couponId, $start_time, $end_time)
    {
        $couponUser              = CouponUser::new();
        $couponUser->user_id     = $userId;
        $couponUser->coupon_id   = $couponId;
        $couponUser->start_time  = $start_time;
        $couponUser->end_time    = $end_time;
        $couponUser->add_time    = Carbon::now()->toDateTimeString();
        $couponUser->update_time = Carbon::now()->toDateTimeString();
        $couponUser->save();
    }

    /**
     * @param $couponId
     * @param $userId
     * @return int
     * 获取用户领取某种优惠券的数量
     */
    public function getUserCouponsCount($couponId, $userId)
    {
        return CouponUser::query()->where([
            'deleted' => 0, 'coupon_id' => $couponId, 'user_id' => $userId
        ])->count('id');
    }

    /**
     * @return int
     */
    public function getCouponUserTotalByCouponId($couponId)
    {
        return CouponUser::query()->where(['deleted' => 0, 'coupon_id' => $couponId])->count('id');
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null|Coupon
     * 根据优惠券Id获取优惠券
     */
    public function getCoupon($id)
    {
        return Coupon::query()->find($id);
    }

    /**
     * @param $id
     * @return CouponUser|CouponUser[]|Builder|Builder[]|Collection|Model|null
     * 获取用户优惠券
     */
    public function getCouponUser($id)
    {
        return CouponUser::query()->find($id);
    }


    /**
     * @param  PageInput  $page
     * @param  string[]  $column
     * @return LengthAwarePaginator
     * 获取优惠列表
     */
    public function getCouponList(PageInput $page, $column = ['*'])
    {
        return Coupon::query()->select($column)->where('type',
            Constant::COUPON_TYPE_COMMON)->where('status',
            Constant::COUPON_STATUS_NORMAL)->orderBy($page->sort, $page->order)->paginate($page->limit, $column, 'page',
            $page->page);
    }

    /**
     * @param  PageInput  $page
     * @param $status
     * @param $userId
     * @param  string[]  $column
     * @return LengthAwarePaginator
     * 获取用户的优惠券
     */
    public function getMyCouponList(PageInput $page, $status, $userId, $column = ['*'])
    {
        return CouponUser::query()->where('user_id', $userId)->where('status',
            $status)->orderBy($page->sort, $page->order)->paginate($page->limit, $column, 'page', $page->page);
    }

    /**
     * @param $ids
     * @return Builder[]|Collection
     * 获取优惠券
     */
    public function getCouponsByIds($ids)
    {
        return Coupon::query()->whereIn('id', $ids)->get();
    }

}
