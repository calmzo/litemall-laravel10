<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;

class CatalogServices extends BaseServices
{

    public function getL1List()
    {
        $res = Category::query()->where(['level' => 'L1', 'deleted' => 0])->get();
        return $res;
    }

    public function getL2List($pid)
    {
        $res = Category::query()->where(['pid' => $pid, 'deleted' => 0])->get();
        return $res;
    }

    public function findById($id)
    {
        return Category::query()->find($id);
    }


    public function getL2ListByIds(array $ids)
    {
        if (empty($ids)) {
            return collect([]);
        }
        return Category::query()->whereIn('id', $ids)->get();
    }


}

