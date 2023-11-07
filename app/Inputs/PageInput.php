<?php

namespace App\Inputs;

use Illuminate\Validation\Rule;

class PageInput extends Input
{
    public $page = 1;
    public $limit = 10;
    public $sort = 'add_time';
    public $order = 'desc';


    /**
     * @return array
     */
    public function rules()
    {
        return [
            'page' => 'integer',
            'limit' => 'integer',
            'sort' => 'string',
            'order' => Rule::in(['desc', 'asc']),
        ];
    }

    /**
     * @return array|string[]
     */
    public function message()
    {
        return [
            'categoryId.required' => 'categoryId不能为空',
            'categoryId.integer' => 'categoryId必须为数字',
        ];
    }

}
