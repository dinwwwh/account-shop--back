<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /**
         * Must be define when make it
         * account_type_id, status_code,
         *
         * if status_code === 480, 840, 880
         * need state: 'status_code' 'approved_at' 'approver_id'
         *
         * if status_code === 840, 880
         * need state: 'buyer_id', 'sold_at'
         */
        /**
         * Properties will receive default value
         * approver_id, buyer_id,
         * sold_at_price, sold_at,
         *
         */
        return [
            'username' => 'tk' . Str::random(40),
            'password' => Str::random(40),
            'cost' => rand(10000, 99999),
            'description' => $this->faker->title,
        ];
    }

    /**
     * last step to generate
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function finalize()
    {
        return $this->state(function (array $attributes) {
            return [
                // Relate account_type_id
                // 'game_id' => AccountType::find($attributes['account_type_id']())->game->getKey(),
            ];
        });
    }
}
