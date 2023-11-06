<?php

namespace App\Models\Goods;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends BaseModel
{
    use HasFactory;

//    protected $table = 'comment';

    protected $fillable = [];

    protected $casts = [
        'pic_urls' => 'array'
    ];

}
