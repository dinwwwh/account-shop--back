<?php

namespace Database\Factories;

use App\Models\Config;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Config::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'key' => Str::random() . time(),
            'data' => Str::random(),
            'description' => Str::random(40),
            'structure_description' => Str::random(40),
            'rules_of_data' => [],
            'public' => rand(0, 1),
        ];
    }
}
