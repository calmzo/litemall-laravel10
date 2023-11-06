<?php

namespace App\Models\Goods;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsProduct extends BaseModel
{
    use HasFactory;

//    protected $table = 'goods_product';

    protected $fillable = [];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'float',

    ];

}
