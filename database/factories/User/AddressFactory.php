<?php

namespace Database\Factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => 0,
            'province'       => '安徽省',
            'city'           => '六安市',
            'county'        => '舒城县',
            'address_detail' => fake()->streetAddress,
            'area_code'      => '',
            'postal_code'    => fake()->postcode,
            'tel'            => fake()->phoneNumber,
            'is_default'     => 0
        ];
    }
}
