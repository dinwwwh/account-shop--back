<?php

namespace Database\Seeders\ForTests;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AccountAction;
use App\Models\AccountFee;
use App\Models\AccountInfo;
use App\Models\AccountStatus;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\GameInfo;
use App\Models\User;
use Arr;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ModuleGameSeederCopy extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::inRandomOrder()->get() as $user) {
            Auth::logout();
            Auth::setUser($user);

            $numberOfUsers = User::count();
            $game = Game::factory()
                ->has(
                    GameInfo::factory()
                        ->count(6),
                    'gameInfos'
                )
                ->has(
                    AccountType::factory()
                        ->has(AccountInfo::factory()->count(6), 'accountInfos')
                        ->has(AccountAction::factory()->count(6), 'accountActions')
                        ->has(AccountFee::factory()->count(4), 'AccountFees')
                        ->count(3),
                    'accountTypes'
                )
                ->create();


            foreach ($game->gameInfos as $gameInfos) {
                $gameInfos->rule->requiredUsers()->attach(rand(1, $numberOfUsers));
                $gameInfos->rule->unrequiredUsers()->attach(rand(1, $numberOfUsers));
            }

            foreach ($game->accountTypes as $accountType) {
                $accountType->usableUsers()->attach(rand(1, $numberOfUsers), ['status_code' => Arr::random([0, 440, 480])]);
                $approverId = User::inRandomOrder()->whereNotIn('id', [Auth::user()->getKey()])
                    ->first()->getKey();

                foreach ($accountType->accountInfos as $accountInfo) {
                    $accountInfo->rule->requiredUsers()->attach(rand(1, $numberOfUsers));
                    $accountInfo->rule->unrequiredUsers()->attach(rand(1, $numberOfUsers));
                }

                foreach ($accountType->accountActions as $accountAction) {
                    $accountAction->rule->requiredUsers()->attach(rand(1, $numberOfUsers));
                    $accountAction->rule->unrequiredUsers()->attach(rand(1, $numberOfUsers));
                }

                $accounts = Account::factory()
                    ->count(5)
                    ->for($accountType, 'accountType')
                    ->has(AccountStatus::factory()->count(2), 'accountStatuses')
                    ->create();

                foreach ($accounts as $account) {
                    if (
                        in_array($account->latestAccountStatus->code, config('account.buyer.readable_login_infos_status_codes', []))
                    ) {
                        $account->buyer_id = User::inRandomOrder()->first()->getKey();
                        $account->sold_at = now();
                        $account->save();
                    }

                    if (
                        in_array($account->latestAccountStatus->code, config('account.status_codes_approving', []))
                    ) {
                        $account->latestAccountStatus->update(
                            [
                                'creator_id' => $approverId,
                            ]
                        );
                        $accountType->approvableUsers()->attach(
                            $approverId,
                            ['status_code' => 480]
                        );
                    }
                }
            }
        }
    }
}
