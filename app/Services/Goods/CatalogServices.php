<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * @param $pid
     * @return Builder[]|Collection
     * 根据一级分类的ID获取二级分类的数据
     */
    public function getL2ListDataByPid($pid)
    {
        return Category::query()->where('pid', $pid)->where('level', 'L2')->get();
    }

    /**
     * @param $pid
     * @return Builder[]|Collection
     * 根据分类的父类ID获取商品的数据
     */
    public function getCategoryByPId($pid)
    {
        return Category::query()->where('pid', $pid)->get();
    }

    /**
     * @param $ids
     * @return Builder[]|Collection
     * 根据主键ID（数组）获取分类数据
     */
    public function getCategoryByIds($ids)
    {
        return Category::query()->whereIn('id', $ids)->get();
    }

}

