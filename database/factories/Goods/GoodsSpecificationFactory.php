<?php

namespace Database\Factories\Goods;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goods\GoodsSpecification>
 */
class GoodsSpecificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goods_id'      => 0,
            'specification' => '规格',
            'value'         => '标准'
        ];
    }
}
