<?php

namespace Tests\Feature\Account;

use App\Models\AccountType;
use App\Models\Game;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class Helper extends TestCase
{
    public function makeDataForAccountInfos(AccountType $accountType)
    {
        $accountInfos = $accountType->accountInfos;
        $data = [];

        foreach ($accountInfos as $accountInfo) {
            if ($accountInfo->rule->isRequired(Role::find('tester'))) {
                if ($accountInfo->rule->datatype == 'string') {
                    $data['id' . $accountInfo->getKey()] = Str::random(10);
                } elseif ($accountInfo->rule->datatype == 'integer') {
                    $data['id' . $accountInfo->getKey()] = rand(1, 01000);
                }
            }
        }

        return $data;
    }

    public function makeDataForAccountActions(AccountType $accountType)
    {
        $accountActions = $accountType->accountActions;
        $data = [];

        foreach ($accountActions as $accountAction) {
            if ($accountAction->isRequired(Role::find('tester'))) {
                $data['id' . $accountAction->getKey()] = true;
            }
        }

        return $data;
    }

    public function makeDataForGameInfos(Game $game)
    {
        $gameInfos = $game->gameInfos;
        $data = [];

        foreach ($gameInfos as $gameInfo) {
            if ($gameInfo->rule->isRequired(Role::find('tester'))) {
                if ($gameInfo->rule->datatype == 'string') {
                    $data['id' . $gameInfo->getKey()] = Str::random(10);
                } elseif ($gameInfo->rule->datatype == 'integer') {
                    $data['id' . $gameInfo->getKey()] = rand(1, 01000);
                }
            }
        }

        return $data;
    }
}
