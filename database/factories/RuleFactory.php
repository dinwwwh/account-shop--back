<?php

namespace Database\Factories;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class RuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'placeholder' => Str::random(20),
            'datatype' => Arr::random(['string', 'integer']),
            'required' => rand(0, 99) < 70 ? true : false,
        ];
    }
}
