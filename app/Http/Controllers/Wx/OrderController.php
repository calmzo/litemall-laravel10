<?php

namespace App\Http\Controllers\Wx;

use App\Models\Order\{Order, OrderGoods};
use App\Utils\{CodeResponse, Constant};
use Illuminate\Support\Facades\{Cache, DB, Log};
use Symfony\Component\HttpFoundation\{Response, RedirectResponse};
use Yansongda\Pay\Exceptions\{InvalidArgumentException, InvalidConfigException, InvalidSignException};
use App\Exceptions\BusinessException;
use App\Inputs\OrderGoodsSubmit;
use App\Inputs\PageInput;
use App\Services\Order\OrderServices;
use App\Services\Promotion\GrouponServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Throwable;
use Yansongda\LaravelPay\Facades\Pay;

class OrderController extends WxController
{
    protected $except = ['wxNotify', 'alipayNotify'];

    /**
     * @return RedirectResponse
     * @throws BusinessException
     * H5支付
     */
    public function h5pay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderServices::getInstance()->getPayWxOrder($this->userId(), $orderId);
        return Pay::wechat()->wap($order);
    }

    /**
     * @return Response
     * @throws BusinessException
     * 支付宝支付
     */
    public function h5alipay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderServices::getInstance()->getAlipayPayOrder($this->userId(), $orderId);
        return Pay::alipay()->wap($order);
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     * 获取订单的列表
     */
    public function list()
    {
        $page = PageInput::new();
        $showType = $this->verifyEnum('showType', 0, array_keys(Constant::ORDER_SHOW_TYPE_STATUS_MAP));
        $status = Constant::ORDER_SHOW_TYPE_STATUS_MAP[$showType];
        $orderListsWithPage = OrderServices::getInstance()->getOrderList(userId: $this->userId(),
            page: $page,
            status: $status);
        $orderLists = collect($orderListsWithPage->items());
        $orderIds = $orderLists->pluck('id')->toArray();

        if (empty($orderIds)) {
            return $this->successPaginate($orderListsWithPage);
        }

        //准备数据
        $grouponOrderIds = GrouponServices::getInstance()->getGrouponOrderByOrderIds($orderIds);
        $orderGoodsLists = OrderServices::getInstance()->getOrderGoodsListsByOrderIds($orderIds)->groupBy('order_id');
        $list = $orderLists->map(function (Order $order) use ($grouponOrderIds, $orderGoodsLists) {
            /** @var Collection $goodsList */
            $goodsList = $orderGoodsLists->get($order->id);
            $goodsList = $goodsList->map(function (OrderGoods $orderGoods) {
                return OrderServices::getInstance()->coverOrderGoods($orderGoods);
            });
            return OrderServices::getInstance()->coverOrder(
                order: $order,
                grouponOrders: $grouponOrderIds,
                goodsList: $goodsList);
        });

        $data = $this->paginate($orderListsWithPage, $list);
        return $this->success($data);
    }


    /**
     * @return JsonResponse
     * @throws BusinessException
     */
    public function detail()
    {
        $orderId = $this->verifyId('orderId');
        $detail = OrderServices::getInstance()->detail($this->userId(), $orderId);
        return $this->success($detail);
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     * 提交订单
     */
    public function submit()
    {
        /** @var OrderGoodsSubmit $input */
        $input = OrderGoodsSubmit::new();
        $lock_key = sprintf("order_submit_%s_%s", $this->userId(), md5(serialize($input)));
        $lock = Cache::lock($lock_key);

        //加上锁，防止重复请求
        if (!$lock->get()) {
            return $this->fail(CodeResponse::FAIL, '请勿重复请求');
        }

        $order = DB::transaction(function () use ($input) {
            return OrderServices::getInstance()->submit($this->userId(), $input);
        });

        //释放锁
        $lock->release();

        return $this->success([
            'orderId' => $order->id,
            'grouponLikeId' => $input->grouponLinkId
        ]);
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     * 用户退款
     */
    public function refund()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->refund($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     * 删除订单
     */
    public function delete()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->delete($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     * 用户确认收货
     */
    public function confirm()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->confirm($this->userId(), $orderId);
        return $this->success();
    }


    /**
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     * 用户主动取消订单
     */
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderServices::getInstance()->userCancel($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * @return Response
     * @throws InvalidSignException
     * @throws Throwable
     * @throws InvalidConfigException
     * 支付宝支付回调
     */
    public function alipayNotify()
    {
        $data = Pay::alipay()->verify()->toArray();
        Log::info('alipayNotify:' . $data);
        DB::transaction(function ($data) {
            OrderServices::getInstance()->alipayNotify($data);
        });
        return Pay::alipay()->success();
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     * 微信回调通知
     */
    public function wxNotify()
    {
        $data = Pay::wechat()->verify();
        $data = $data->toArray();
        DB::transaction(function () use ($data) {
            OrderServices::getInstance()->wxNotify($data);
        });
        return Pay::wechat()->success();
    }
}
