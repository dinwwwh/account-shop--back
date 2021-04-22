<?php

namespace Database\Factories;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DiscountCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountCode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'discount_code' => 'discountCode-' . Str::random(40) . now(),
            'price' => rand(0, 10000),
            'buyable' => true,
            'name' => $this->faker->name,
            'description' => $this->faker->name . Str::random(40),

            'maximum_price' => 500000,
            'minimum_price' => 100000,
            'maximum_discount' => 100000,
            'minimum_discount' => 10000,
            'percentage_discount' => rand(0, 100),
            'direct_discount' => rand(0, 10000),
        ];
    }
}
