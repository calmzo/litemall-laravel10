<?php

namespace App\Models\Goods;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Footprint extends BaseModel
{
    use HasFactory;

//    protected $table = 'footprint';

    protected $fillable = [
        'user_id',
        'goods_id'
    ];


}
