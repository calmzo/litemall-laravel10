<?php

namespace App\Services\Goods;

use App\Models\Goods\GoodsAttribute;
use App\Services\BaseServices;

class GoodsAttributeServices extends BaseServices
{
    public function queryListByGid($goodsId)
    {
        return GoodsAttribute::query()->where('goods_id', $goodsId)->where('deleted', 0)->get();

    }

}

