<?php

namespace Database\Factories;

use App\Models\GameInfo;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameInfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GameInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Must define game_id
        return [
            'name' => $this->faker->name,
            'slug' => fn ($attrs) => Str::slug($attrs['name']),
            'description' => Str::random(40),
            'rule_id' => Rule::factory(),
        ];
    }
}
