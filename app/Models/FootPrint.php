<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\FootPrint
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint query()
 * @mixin \Eloquent
 */
class FootPrint extends BaseModel
{
    use HasFactory;

    protected $table = 'footprint';

    protected $fillable = [
        'user_id',
        'goods_id'
    ];


}
