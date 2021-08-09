<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\User;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class CouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $buyable = rand(0, 1);
        $price = $buyable ? rand(0, 90000) : null;
        $usableAt = now()->subMinutes(rand(1, 55))->addMinutes(rand(1, 55));
        $offeredAt = now()->subMinutes(rand(1, 55))->addMinutes(rand(1, 55));

        return [
            'code' => Str::orderedUuid(),
            'name' => $this->faker->name,
            'description' => $this->faker->name . Str::random(40),
            'amount' => Arr::random([null, rand(1, 99)]),
            'used_amount' => 0,

            'maximum_value' => rand(200000, 500000),
            'minimum_value' => rand(0, 100000),
            'maximum_discount' => rand(20000, 50000),
            'minimum_discount' => rand(0, 10000),
            'percentage_discount' => rand(0, 100),
            'direct_discount' => rand(0, 10000),
            'usable_at' => Arr::random([null, $usableAt]),
            'usable_closed_at' => Arr::random([null, $usableAt->copy()->addMinutes(rand(1, 55))]),

            'price' => $price,
            'offered_at' => Arr::random([null, $offeredAt]),
            'offer_closed_at' => Arr::random([null, $offeredAt->copy()->addMinutes(rand(1, 55))]),

            'creator_id' => User::inRandomOrder()->first()->getKey(),
            'latest_updater_id' => User::inRandomOrder()->first()->getKey(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {

        return $this->afterMaking(function (Coupon $coupon) {
            //
        })->afterCreating(function (Coupon $coupon) {

            if (!is_null($coupon->price)) {
                $coupon->buyers()->attach(
                    User::inRandomOrder()->first()
                );
            }
        });
    }
}
