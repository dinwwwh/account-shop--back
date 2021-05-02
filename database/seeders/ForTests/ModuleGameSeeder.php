<?php

namespace Database\Seeders\ForTests;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AccountAction;
use App\Models\AccountInfo;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\GameInfo;
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
                GameInfo::factory()
                    ->count(6),
                'gameInfos'
            )
            ->has(
                AccountType::factory()
                    ->count(3)
                    ->has(AccountInfo::factory()->count(6), 'accountActions')
                    ->has(AccountAction::factory()->count(6), 'accountInfos')
                    ->a28s(),
                'accountTypes'
            )
            ->create();

        foreach ($games as $game) {
            $game->rolesCanCreatedGame()->sync('tester');

            foreach ($game->accountTypes as $accountType) {
                $accountType->allowRole('tester', rand(1, 100) < 70 ? 0 : 440);
                foreach ($accountType->accountInfos as $accountInfo) {
                    if (is_null($accountInfo->rule->required)) {
                        $accountInfo->rule->requiredRoles()->attach('tester');
                    }
                }
                foreach ($accountType->accountActions as $accountAction) {
                    $accountAction->requiredRoles()->attach('tester');
                }
            }

            foreach ($game->gameInfos as $gameInfos) {
                if (is_null($gameInfos->rule->required)) {
                    $gameInfos->rule->requiredRoles()->attach('tester');
                }
            }


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
