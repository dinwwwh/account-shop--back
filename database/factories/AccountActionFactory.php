<?php

namespace Database\Factories;

use App\Models\AccountAction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class AccountActionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountAction::class;

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
            'video_path' => 'no thing this is test',
            'required' => Arr::random([null, true, false]),
        ];
    }
}
