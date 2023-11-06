<?php

namespace App\Services\Goods;

use App\Services\BaseServices;
use App\Models\Goods\GoodsProduct;

class GoodsProductServices extends BaseServices
{
    public function queryListByGid($goodsId)
    {
        return GoodsProduct::query()->where('goods_id', $goodsId)->where('deleted', 0)->get();

    }

}

