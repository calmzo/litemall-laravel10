<?php

namespace Database\Factories\Goods;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goods\Goods>
 */
class GoodsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goods_sn'      => fake()->word,
            'name'          => "测试商品".fake()->word,
            'category_id'   => 1008009,
            'brand_id'      => 0,
            'gallery'       => [],
            'keywords'      => "",
            'brief'         => '测试',
            'is_on_sale'    => 1,
            'sort_order'    => fake()->numberBetween(1, 999),
            'pic_url'       => 'http://yanxuan.nosdn.127.net/3bd73b7279a83d1cbb50c0e45778e6d6.png',
            'share_url'     => fake()->url,
            'is_new'        => fake()->boolean,
            'is_hot'        => fake()->boolean,
            'unit'          => "件",
            'counter_price' => 919,
            'retail_price'  => 899,
            'detail'        => fake()->text
        ];
    }
}
