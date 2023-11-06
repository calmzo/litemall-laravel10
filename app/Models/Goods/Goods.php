<?php

namespace App\Models\Goods;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goods extends BaseModel
{
    use HasFactory;

    protected $table = 'goods';

    protected $fillable = [];

    protected $casts = [
        'counter_price' => 'float',
        'retail_price' => 'float',
        'is_hot' => 'boolean',
        'is_new' => 'boolean',
        'gallery' => 'array',
        'isOnSale' => 'boolean',


    ];

}
