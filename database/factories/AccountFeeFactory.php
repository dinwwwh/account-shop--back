<?php

namespace Database\Factories;

use App\Models\AccountFee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountFee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /**
         * Must be define
         * 'account_type_id'
         */

        return [
            'maximum_cost' => 500000,
            'minimum_cost' => 10000,
            'maximum_fee' => 100000,
            'minimum_fee' => 0,
            'percentage_cost' => rand(0, 100),
            'direct_fee' => rand(0, 10000),
        ];
    }

    /**
     * a28s = autoCreateSmallerRelationships
     *
     */
    public function a28s()
    {
        return $this;
    }
}
