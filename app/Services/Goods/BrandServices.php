<?php

namespace App\Services\Goods;

use App\Models\Goods\Brand;
use App\Services\BaseServices;

class BrandServices extends BaseServices
{

    public function getBrand($id)
    {
        return Brand::query()->find($id);
    }

    public function getBrandByLimit($limit, $columns = ['*'], $offset = 0)
    {
        return Brand::query()->offset($offset)->limit($limit)->get($columns);
    }

    public function getBrandList(int $page, int $limit, $sort, $order)
    {
        $query = Brand::query()->where('deleted', 0);
        if (!empty($sort) && !empty($order)) {
            $query = $query->orderBy($sort, $order);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

}

