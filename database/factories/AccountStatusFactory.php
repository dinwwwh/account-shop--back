<?php

namespace Database\Factories;

use App\Models\AccountStatus;
use App\Models\User;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => Arr::random(config('account.status_codes', [])),
        ];
    }
}
