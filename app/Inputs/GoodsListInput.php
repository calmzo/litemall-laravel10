<?php

namespace App\Inputs;

use Illuminate\Validation\Rule;

class GoodsListInput extends Input
{
    public $categoryId;
    public $brandId;
    public $keyword;
    public $isNew;
    public $isHot;
    public $page = 1;
    public $limit = 10;
    public $sort = 'add_time';
    public $order = 'desc';


    public function rules()
    {
        return [
            'categoryId' => 'integer',
            'brandId' => 'integer',
            'keyword' => 'sometimes|regex:/^1[345789][0-9]{9}$/',
            'isNew'      => 'boolean',
            'isHot'      => 'boolean',
            'page'       => 'integer',
            'limit'      => 'integer',
            'sort'       => Rule::in(['add_time', 'retail_price', 'name']),
            'order'      => Rule::in(['desc', 'asc']),
        ];
    }

    public function message()
    {
        return [

        ];
    }

}
