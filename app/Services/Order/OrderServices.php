<?php

namespace App\Services\Order;

use App\Exceptions\BusinessException;
use App\Inputs\OrderGoodsSubmit;
use App\Inputs\PageInput;
use App\Jobs\OverTimeCancelOrder;
use App\Models\Cart\Cart;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Models\Promotion\Coupon;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponServices;
use App\Services\Promotion\GrouponServices;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use App\Utils\CodeResponse;
use App\Utils\Constant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * @param $userId
     * @param  PageInput  $page
     * @param $status
     * @param  string[]  $column
     * @return LengthAwarePaginator
     * 获取订单列表信息
     */
    public function getOrderList($userId, PageInput $page, $status, $column = ['*'])
    {
        return Order::query()->where('user_id', $userId)
            ->when(!empty($status), function (Builder $builder) use ($status) {
                return $builder->whereIn('order_status', $status);
            })->orderBy($page->sort, $page->order)->paginate($page->limit, $column, 'page', $page->page);
    }
    public function coverOrder(Order $order, $grouponOrders, $goodsList)
    {
        return [
            "id"              => $order->id,
            "orderSn"         => $order->order_sn,
            "actualPrice"     => $order->actual_price,
            "orderStatusText" => Constant::ORDER_STATUS_TEXT_MAP[$order->order_status] ?? '',
            "handleOption"    => $order->getCanHandleOptions(),
            "aftersaleStatus" => $order->aftersale_status,
            "isGroupin"       => in_array($order->id, $grouponOrders),
            "goodsList"       => $goodsList,
        ];
    }
    public function coverOrderGoods(OrderGoods $orderGoods)
    {
        return [
            "id"             => $orderGoods->id,
            "goodsName"      => $orderGoods->goods_name,
            "number"         => $orderGoods->number,
            "picUrl"         => $orderGoods->pic_url,
            "specifications" => $orderGoods->specifications,
            "price"          => $orderGoods->price
        ];
    }

    /**
     * @param $userId
     * @param $orderId
     * @return array
     * @throws BusinessException
     * 订单详情
     */
    public function detail($userId, $orderId)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            $this->throwBusinessException(CodeResponse::ORDER_UNKNOWN);
        }

        $detail = Arr::only($order->toArray(), [
            "id",
            "orderSn",
            "message",
            "addTime",
            "consignee",
            "mobile",
            "address",
            "goodsPrice",
            "couponPrice",
            "freightPrice",
            'actualPrice',
            "aftersaleStatus"
        ]);

        $detail['orderStatusText'] = Constant::ORDER_STATUS_TEXT_MAP[$order->order_status];
        $detail['handleOption']    = $order->getCanHandleOptions();

        $goodsList         = $this->getOrderGoodList($orderId);
        $detail['expCode'] = $order->ship_channel;
        $detail['expNo']   = $order->ship_sn;
        $detail['expName'] = ExpressServices::getInstance()->getExpressName($order->ship_channel);
        $express           = []; //todo

        return [
            'orderInfo'   => $detail,
            'orderGoods'  => $goodsList,
            'expressInfo' => $express
        ];
    }

    /**
     * @param $userId
     * @param $orderId
     * @return Order|Order[]|Builder|Builder[]|Collection|Model|null
     * 获取订单的信息
     */
    public function getOrderByUserIdAndId($userId, $orderId)
    {
        return Order::query()->where('user_id', $userId)->find($orderId);
    }

    /**
     * @param $orderId
     * @param  string[]  $column
     * @return OrderGoods[]|Builder[]|Collection
     * 获取订单商品的列表
     */
    public function getOrderGoodList($orderId, $column = ['*'])
    {
        return OrderGoods::query()->whereOrderId($orderId)->get($column);
    }

    /**
     * @param $userId
     * @param  OrderGoodsSubmit  $input
     * @return Order
     * @throws BusinessException
     */
    public function submit($userId, OrderGoodsSubmit $input)
    {
        // 验证团购活动是否有效
        if (!empty($input->grouponRulesId)) {
            GrouponServices::getInstance()->checkGrouponRulesValid($userId, $input->grouponRulesId);
        }
        // 获取收获地址
        $address = AddressServices::getInstance()->getUserAddress($userId, $input->addressId);
        if (empty($address)) {
            $this->throwBadArgumentValue();
        }
        // 获取购物车的商品列表
        $checkedGoodList = CartServices::getInstance()->getCheckedGoodsList($userId, $input->cartId);
        // 计算商品的总价格（团购优惠金额，货品价格，优惠券优惠价格，运费）
        $grouponPrice      = 0;
        $checkedGoodsPrice = CartServices::getInstance()->getCartPriceCutGroupon($checkedGoodList,
            $input->grouponRulesId, $grouponPrice);
        // 获取优惠券面额
        $couponPrice = 0;
        if ($input->couponId > 0) {
            /** @var Coupon $coupon */
            $coupon     = CouponServices::getInstance()->getCoupon($input->couponId);
            $couponUser = CouponServices::getInstance()->getCouponUser($input->userCouponId);
            $is         = CouponServices::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
            if ($is) {
                $couponPrice = $coupon->discount;
            }
        }
        // 运费
        $freightPrice = SystemServices::getInstance()->getFreightPrice($checkedGoodsPrice);
        // 计算订单金额
        $orderTotalPrice = bcadd($checkedGoodsPrice, $freightPrice, 2);
        $orderTotalPrice = bcsub($orderTotalPrice, $couponPrice, 2);
        $orderTotalPrice = max(0, $orderTotalPrice);
        // 保存订单
        $order                 = new Order();
        $order->user_id        = $userId;
        $order->order_sn       = $this->generateOrderSn();
        $order->order_status   = Constant::ORDER_STATUS_CREATE;
        $order->consignee      = $address->name;
        $order->address        = $address->province . $address->city . $address->county . " " . $address->address_detail;
        $order->message        = $input->message ?? " ";
        $order->goods_price    = $checkedGoodsPrice;
        $order->freight_price  = $freightPrice;
        $order->integral_price = 0;
        $order->mobile         = "";
        $order->coupon_price   = $couponPrice;
        $order->order_price    = $orderTotalPrice;
        $order->actual_price   = $orderTotalPrice;
        $order->groupon_price  = $grouponPrice;
        $order->save();
        // 写入订单商品记录（快照）
        $this->saveOrderGoods($checkedGoodList, $order->id);
        // 删除购物车商品记录
        CartServices::getInstance()->clearCartGoods($userId, $input->cartId);
        // 减库存(重点：乐观锁+防止重复请求)
        $this->reduceProductsStock($checkedGoodList);
        // 设置优惠券的状态
        // 添加团购记录
        GrouponServices::getInstance()->saveGrouponData($input->grouponRulesId, $userId, $order->id,
            $input->grouponLinkId);
        // 设置订单支付超时取消订单任务
        dispatch(new OverTimeCancelOrder($userId, $order->id));
        return $order;
    }

    /**
     * @return mixed
     * @throws BusinessException
     * 获取订单编号
     */
    public function generateOrderSn()
    {
        return retry(5, function () {
            $date    = date('YmdHis');
            $orderSn = $date . Str::random(6);
            if ($this->checkOrderSnValid($orderSn)) {
                Log::warning("订单号获取失败：" . $orderSn);
                $this->throwBusinessException(\App\CodeResponse::FAIL, '订单号获取失败');
            }
            return $orderSn;
        });
    }

    /**
     * @param $checkedGoodList
     * @param $orderId
     * 保存订单的快照
     */
    public function saveOrderGoods($checkedGoodList, $orderId)
    {
        /** @var Cart $cart */
        foreach ($checkedGoodList as $cart) {
            $orderGoods                 = OrderGoods::new();
            $orderGoods->order_id       = $orderId;
            $orderGoods->goods_id       = $cart->goods_id;
            $orderGoods->goods_sn       = $cart->goods_sn;
            $orderGoods->product_id     = $cart->product_id;
            $orderGoods->goods_name     = $cart->goods_name;
            $orderGoods->pic_url        = $cart->pic_url;
            $orderGoods->price          = $cart->price;
            $orderGoods->number         = $cart->number;
            $orderGoods->specifications = $cart->specifications;
            $orderGoods->save();
        }
    }

    /**
     * @param  Collection  $checkProductList
     * @throws BusinessException
     * 减去库存，注意并发和重复请求的问题，即幂等性（对于同一个系统，多次重复请求的结果需要是一样的）
     */
    public function reduceProductsStock(Collection $checkProductList)
    {
        $productIds = $checkProductList->pluck('product_id')->toArray();
        $products   = GoodsServices::getInstance()->getGoodsProductsByIds($productIds)->keyBy('id');
        foreach ($checkProductList as $cart) {
            /** @var GoodsProduct $product */
            $product = $products->get($cart->product_id);
            if (empty($product)) {
                $this->throwBusinessException();
            }
            if ($product->number < $cart->number) {
                $this->throwBusinessException(\App\CodeResponse::GOODS_NO_STOCK);
            }
            $row = GoodsServices::getInstance()->reduceStock($product->id, $cart->number);
            if ($row == 0) {
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
        }
    }

    /**
     * @param $userId
     * @param $orderId
     * @return Order|Order[]|Builder|Builder[]|Collection|Model|null
     * @throws BusinessException
     * @throws Throwable
     * 用户退款
     */
    public function refund($userId, $orderId)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);

        if (empty($order)) {
            $this->throwBusinessException();
        }

        if (!$order->canRefundHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能申请退款哦');
        }

        $order->order_status = Constant::ORDER_STATUS_REFUND;

        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
        //todo 发通知给管理员进行退款处理
        return $order;
    }

    /**
     * @param $userId
     * @param $orderId
     * @return bool
     * @throws BusinessException
     */
    public function delete($userId, $orderId)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);

        if (empty($order)) {
            $this->throwBusinessException();
        }

        if (!$order->canDeleteHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能被删除哦');
        }

        $order->delete();

        //todo 处理订单售后的信息
        return true;
    }

    /**
     * @param $userId
     * @param $orderId
     * @param  false  $isAuto
     * @return Order|Order[]|Builder|Builder[]|Collection|Model|null
     * @throws BusinessException
     * @throws Throwable
     * 确认收货
     */
    public function confirm($userId, $orderId, $isAuto = false)
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);

        if (empty($order)) {
            $this->throwBusinessException();
        }

        if (!$order->canConfirmHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能被确认收货');
        }

        $order->comments     = $this->countOrderGoods($orderId);
        $order->order_status = $isAuto ? Constant::ORDER_STATUS_AUTO_CONFIRM : Constant::ORDER_STATUS_CONFIRM;
        $order->confirm_time = now()->toDateTimeString();

        if ($order->cas() == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        return $order;
    }

    /**
     * @param $orderId
     * @return int
     * 计算订单中商品的数量
     */
    private function countOrderGoods($orderId)
    {
        return OrderGoods::whereOrderId($orderId)->count(['id']);
    }

    /**
     * @param $userId
     * @param $orderId
     * @return mixed
     * @throws Throwable
     * 用户取消订单
     */
    public function userCancel($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancel($userId, $orderId, 'user');
        });
        return true;
    }

    /**
     * @param $userId
     * @param $orderId
     * @param  string  $role  支持 user / admin / system
     * @return bool
     * @throws BusinessException
     * 取消订单
     */
    private function cancel($userId, $orderId, $role = 'user')
    {
        $order = $this->getOrderByUserIdAndId($userId, $orderId);

        if (is_null($orderId)) {
            $this->throwBusinessException();
        }

        if (!$order->canCancelHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '订单不能取消');
        }

        switch ($role) {
            case 'system':
                $order->order_status = Constant::ORDER_STATUS_AUTO_CANCEL;
                break;
            case 'admin':
                $order->order_status = Constant::ORDER_STATUS_ADMIN_CANCEL;
                break;
            default:
                $order->order_status = Constant::ORDER_STATUS_CANCEL;
        }

        if ($order->cas() === 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        $this->addProductStock($orderId);

        return true;
    }

    /**
     * @param $orderId
     * @throws BusinessException
     * 增加产品的库存
     */
    public function addProductStock($orderId)
    {
        $orderGoods = $this->getOrderGoodList($orderId);
        /** @var OrderGoods $orderGood */
        foreach ($orderGoods as $orderGood) {
            $row = GoodsServices::getInstance()->addStock($orderGood->product_id, $orderGood->number);
            if ($row == 0) {
                $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
            }
        }
    }

}

