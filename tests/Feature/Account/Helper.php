<?php

namespace Tests\Feature\Account;

use App\Models\AccountType;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Str;
use Tests\TestCase;

class Helper extends TestCase
{
    public function runFirst()
    {
        $this->refreshDatabase();
    }

    public function makeDataForAccountInfos(AccountType $accountType, User $user)
    {
        $accountInfos = $accountType->accountInfos;
        $data = [];

        foreach ($accountInfos as $accountInfo) {
            if ($accountInfo->rule->isRequired($user)) {
                if ($accountInfo->rule->datatype == 'string') {
                    $data[$accountInfo->getKey()] = ['values' => [Str::random(10)]];
                } elseif ($accountInfo->rule->datatype == 'integer') {
                    $data[$accountInfo->getKey()] = ['values' => [rand(1, 01000)]];
                }
            }
        }

        return $data;
    }

    public function makeDataForAccountActions(AccountType $accountType, User $user)
    {
        $accountActions = $accountType->accountActions;
        $data = [];

        foreach ($accountActions as $accountAction) {
            if ($accountAction->rule->isRequired($user)) {
                $data[$accountAction->getKey()] = ['isDone' => true];
            }
        }

        return $data;
    }

    public function makeDataForGameInfos(Game $game, User $user)
    {
        $gameInfos = $game->gameInfos;
        $data = [];

        foreach ($gameInfos as $gameInfo) {
            if ($gameInfo->rule->isRequired($user)) {
                if ($gameInfo->rule->datatype == 'string') {
                    $data[$gameInfo->getKey()] = ['values' => [Str::random(10)]];
                } elseif ($gameInfo->rule->datatype == 'integer') {
                    $data[$gameInfo->getKey()] = ['values' => [rand(1, 01000)]];
                }
            }
        }

        return $data;
    }

    /**
     * Make fake data for an account used to test
     */
    public function makeAccountData(AccountType $accountType, User $user): array
    {
        $game = $accountType->game;
        $dataOfAccountActions = $this->makeDataForAccountActions($accountType, $user);
        $dataOfAccountInfos = $this->makeDataForAccountInfos($accountType, $user);
        $dataOfGameInfos = $this->makeDataForGameInfos($game, $user);

        return  [
            'username' => Str::random(60),
            'password' => Str::random(60),
            'cost' => rand(20000, 50000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'),
                UploadedFile::fake()->image('avatar4324.jpg'),
            ],
            'rawAccountInfos' => $dataOfAccountInfos,
            'rawAccountActions' => $dataOfAccountActions,
            'rawGameInfos' => $dataOfGameInfos,
            '_requiredModelRelationships' => ['representativeImage', 'otherImages']
        ];
    }

    public function getUserCanUseSomeAccountType(): User
    {
        return User::find(
            DB::table('account_type_user_usable')
                ->inRandomOrder()->first()
                ->user_id
        );
    }
}
