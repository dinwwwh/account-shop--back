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
use App\Models\Rule;
use App\Models\User;
use Arr;
use Auth;
use DB;
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
        $numberOfUsers = User::count();
        $half = (int)($numberOfUsers / 2);
        $firstHalfUsers = User::limit($half)->get();

        $requiredRule = Rule::factory()->state([
            'required' => true,
        ])->create();
        $requiredRule->unrequiredUsers()->sync($firstHalfUsers);

        $unrequiredRule = Rule::factory()->state([
            'required' => false,
        ])->create();
        $unrequiredRule->requiredUsers()->sync($firstHalfUsers);

        $sequenceForRules = new Sequence(
            [
                'rule_id' => $requiredRule
            ],
            [
                'rule_id' => $unrequiredRule
            ]
        );

        $requiredBoolRule = Rule::factory()->state([
            'datatype' => 'boolean',
            'required' => true,
            'allowable_values' => [true]
        ])->create();
        $requiredBoolRule->unrequiredUsers()->sync($firstHalfUsers);

        $unrequiredBoolRule = Rule::factory()->state([
            'datatype' => 'boolean',
            'required' => false,
        ])->create();
        $unrequiredBoolRule->requiredUsers()->sync($firstHalfUsers);

        $sequenceForBoolRules = new Sequence(
            [
                'rule_id' => $requiredBoolRule
            ],
            [
                'rule_id' => $unrequiredBoolRule
            ]
        );

        $users = User::inRandomOrder()->get();
        foreach ($users as $user) {
            Auth::logout();
            Auth::setUser($user);

            $game = Game::factory()
                ->has(
                    GameInfo::factory()
                        ->state($sequenceForRules)
                        ->count(2),
                    'gameInfos'
                )
                ->has(
                    AccountType::factory()
                        ->has(
                            AccountInfo::factory()
                                ->state($sequenceForRules)
                                ->count(2),
                            'accountInfos'
                        )
                        ->has(
                            AccountAction::factory()
                                ->state($sequenceForBoolRules)
                                ->count(2),
                            'accountActions'
                        )
                        ->has(AccountFee::factory()->count(2), 'AccountFees')
                        ->count(2),
                    'accountTypes'
                )
                ->create();




            foreach ($game->accountTypes as $accountType) {
                $accountType->usableUsers()->attach(rand(1, $numberOfUsers), ['status_code' => Arr::random([0, 440, 480])]);
                $approverId = User::inRandomOrder()->whereNotIn('id', [$user->getKey()])
                    ->first()->getKey();
                $accountType->approvableUsers()->attach(
                    $approverId,
                    ['status_code' => 480]
                );

                $accounts = Account::factory()
                    ->count(6)
                    ->for($accountType, 'accountType')
                    ->has(AccountStatus::factory()->state(new Sequence(
                        [
                            'code' => 0,
                        ],
                        [
                            'code' => 200,
                        ],
                        [
                            'code' => 440,
                        ],
                        [
                            'code' => 480,
                        ],
                        [
                            'code' => 840,
                        ],
                        [
                            'code' => 880,
                        ],
                    )), 'accountStatuses')
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
                    }
                }
            }
        }
    }
}
