<?php

namespace App\Services\Goods;

use App\Models\Goods\GoodsAttribute;
use App\Models\Goods\GoodsSpecification;
use App\Services\BaseServices;

class GoodsSpecificationServices extends BaseServices
{
    public function getSpecificationVoList($goodsId)
    {
        $spec = $this->queryListByGid($goodsId)->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v];
        })->values();
    }

    public function queryListByGid($goodsId)
    {
        return GoodsSpecification::query()->where('goods_id', $goodsId)->where('deleted', 0)->get();

    }

}

