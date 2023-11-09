<?php

namespace Database\Factories\Promotion;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion\GrouponRules>
 */
class GrouponRulesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return  [
            'goods_id' => 0,
            'goods_name' => '',
            'pic_url' => '',
            'discount' => 0,
            'discount_member' => 2,
            'expire_time' => now()->addDays(10)->toDateTimeString()
        ];
    }
}
