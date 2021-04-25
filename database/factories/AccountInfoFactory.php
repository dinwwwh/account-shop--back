<?php

namespace Database\Factories;

use App\Models\AccountInfo;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccountInfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'AccountInfo' . Str::random(20),
            'slug' => fn ($attrs) => Str::slug($attrs['name']),
            'description' => Str::random(40),
            'rule_id' => Rule::factory(),
        ];
    }
}
