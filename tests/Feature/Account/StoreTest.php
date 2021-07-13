<?php

namespace Tests\Feature\Account;

use App\Models\Game;
use App\Models\Permission;
use App\Models\User;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class StoreTest extends Helper
{
    public function test_controller()
    {
        $user = $this->makeAuth(
            Permission::all()->pluck('key')->toArray(),
            $this->getUserCanUseSomeAccountType(),
        );
        $accountType =  $user->usableAccountTypes()->inRandomOrder()->first();
        $this->actingAs($user);
        $route = route('account.store', ['accountType' => $accountType]);
        $data = $this->makeAccountData($accountType, $user);

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'username' => $data['username'],
            'password' => $data['password'],
            'cost' => $data['cost'],
            'description' => $data['description'],
        ]);

        foreach ($data['accountInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_account_info', [
                'account_id' => $res->getData()->data->id,
                'account_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value['value']),
            ]);
        }
        foreach ($data['accountActions'] as $key => $value) {
            $this->assertDatabaseHas('account_account_action', [
                'account_id' => $res->getData()->data->id,
                'account_action_id' => (int)trim($key, 'id '),
                'is_done' => (bool)$value['isDone'],
            ]);
        }
        foreach ($data['gameInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_has_game_infos', [
                'account_id' => $res->getData()->data->id,
                'game_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value['value']),
            ]);
        }
    }

    public function test_request_lack_game_infos()
    {
        $count = 0;
        do {
            $user = $this->makeAuth(
                Permission::all()->pluck('key')->toArray(),
                $this->getUserCanUseSomeAccountType(),
            );
            $accountType =  $user->usableAccountTypes()->inRandomOrder()->first();
            $this->actingAs($user);
            $route = route('account.store', ['accountType' => $accountType]);
            $data = $this->makeAccountData($accountType, $user);
            $count++;
            if ($count == 100) return;
        } while (empty($data['gameInfos']));

        unset($data['gameInfos']);
        $this->json('post', $route, $data)
            ->assertStatus(422);
    }

    public function test_request_lack_account_infos()
    {
        do {
            $user = $this->makeAuth(
                Permission::all()->pluck('key')->toArray(),
                $this->getUserCanUseSomeAccountType(),
            );
            $accountType =  $user->usableAccountTypes()->inRandomOrder()->first();
            $this->actingAs($user);
            $route = route('account.store', ['accountType' => $accountType]);
            $data = $this->makeAccountData($accountType, $user);
        } while (empty($data['accountInfos']));

        unset($data['accountInfos']);
        $this->json('post', $route, $data)
            ->assertStatus(422);
    }

    public function test_request_lack_account_actions()
    {
        do {
            $user = $this->makeAuth(
                Permission::all()->pluck('key')->toArray(),
                $this->getUserCanUseSomeAccountType(),
            );
            $accountType =  $user->usableAccountTypes()->inRandomOrder()->first();
            $this->actingAs($user);
            $route = route('account.store', ['accountType' => $accountType]);
            $data = $this->makeAccountData($accountType, $user);
        } while (empty($data['accountActions']));

        unset($data['accountActions']);
        $this->json('post', $route, $data)
            ->assertStatus(422);
    }

    public function test_middleware_success_usable_user()
    {
        $game = Game::inRandomOrder()->first();
        $accountType = $game->accountTypes()->inRandomOrder()->first();
        $user = $this->makeAuth(
            Permission::all()->pluck('key')->toArray(),
            $accountType->usableUsers()->first(),
        );
        $this->actingAs($user);
        $route = route('account.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    public function test_middleware_fail_unusable_user()
    {
        $game = Game::inRandomOrder()->first();
        $accountType = $game->accountTypes()->inRandomOrder()->first();
        $unusableUser = $this->makeAuth(
            Permission::all()->pluck('key')->toArray(),
            $accountType->usableUsers->pluck('id')->toArray(),
        );
        $this->actingAs($unusableUser);

        $route = route('account.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }
}
