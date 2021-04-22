<?php

namespace Database\Seeders\ForTests;

use App\Models\Account;
use App\Models\AccountFee;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ModuleGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $games = Game::factory()
            ->count(10)
            ->has(
                AccountType::factory()
                    ->count(3)
                    ->state([
                        'last_updated_editor_id' => User::all()->random()->getKey(),
                        'creator_id' => User::all()->random()->getKey(),
                    ])
                    ->a28s(),
                'accountTypes'
            )
            ->create();

        foreach ($games as $game) {
            $account = Account::factory()
                ->count(5)
                ->state(new Sequence(
                    [
                        'status_code' => 0,
                    ],
                    [
                        'status_code' => 440,
                    ],
                    fn () => [
                        'status_code' => 480,
                        'approved_at' => now(),
                        'censor_id' => User::inRandomOrder()->first(),
                    ],
                    fn () => [
                        'status_code' => 840,
                        'approved_at' => now(),
                        'censor_id' => User::inRandomOrder()->first(),
                        'buyer_id' => User::inRandomOrder()->first(),
                        'sold_at' => now(),
                    ],
                    fn () => [
                        'status_code' => 880,
                        'approved_at' => now(),
                        'censor_id' => User::inRandomOrder()->first(),
                        'buyer_id' => User::inRandomOrder()->first(),
                        'sold_at' => now(),
                    ],
                ))
                ->for($game->accountTypes->random(), 'accountType')
                ->create();
        }
    }
}
