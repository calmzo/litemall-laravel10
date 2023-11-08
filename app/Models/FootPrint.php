<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\FootPrint
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint query()
 * @property int $id
 * @property int $user_id 用户表的用户ID
 * @property int $goods_id 浏览商品ID
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FootPrint whereUserId($value)
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
