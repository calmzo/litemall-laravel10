<?php

namespace App\Services\Goods;
use Illuminate\Database\Eloquent\{Builder, Collection, Model};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Inputs\PageInput;
use App\Models\Goods\Brand;
use App\Services\BaseServices;

class BrandServices extends BaseServices
{

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     * 获取品牌的详细数据
     */
    public function getBrand($id)
    {
        return Brand::query()->find($id);
    }

    public function getBrandByLimit($limit, $columns = ['*'], $offset = 0)
    {
        return Brand::query()->offset($offset)->limit($limit)->get($columns);
    }

    /**
     * @param PageInput $page
     * @param string[] $columns
     * @return LengthAwarePaginator
     */
    public function getBrandList(PageInput $page, $columns = ['*'])
    {
        return Brand::query()->orderBy($page->sort, $page->order)->paginate($page->limit, $columns, 'page', $page->page);
    }

}

