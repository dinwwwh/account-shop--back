<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountType;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\Game;
use App\Models\Account;
use Illuminate\Http\UploadedFile;

class AccountTest extends TestCase
{
    public function test_freshDatabaseForTest()
    {
        $user = User::inRandomOrder()->first();
        $this->actingAs($user);
        $this->seed(\Database\Seeders\ForTestSeeder::class);

        $this->assertTrue(true);
    }

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

    public function testStore()
    {
        $user = User::inRandomOrder()->first();
        $user->givePermissionTo('create_account');
        $user->assignRole('tester');
        $user->refresh();
        $game = Game::inRandomOrder()->first();

        $accountType = $game->accountTypes->random();
        $route = route('account.store', ['accountType' => $accountType]);
        $dataOfAccountActions = $this->makeDataForAccountActions($accountType);
        $dataOfAccountInfos = $this->makeDataForAccountInfos($accountType);
        $dataOfGameInfos = $this->makeDataForGameInfos($game);
        $data = [
            'roleKey' => 'tester',
            'username' => Str::random(60),
            'password' => Str::random(60),
            'cost' => rand(20000, 50000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'),
                UploadedFile::fake()->image('avatar4324.jpg'),
            ],
            'accountInfos' => $dataOfAccountInfos,
            'accountActions' => $dataOfAccountActions,
            'gameInfos' => $dataOfGameInfos,
            '_requiredModelRelationships' => ['representativeImage', 'otherImages']
        ];

        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('username', $data['username'])
                        ->where('password', $data['password'])
                        ->where('cost', $data['cost'])
                        ->where('description', $data['description'])
                        ->has('representativeImage.path')
                        ->has('otherImages.' . array_key_last($data['images']))
                        ->etc()
                )
        );
        foreach ($data['accountInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_account_info', [
                'account_id' => $res->getData()->data->id,
                'account_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }
        foreach ($data['accountActions'] as $key => $value) {
            $this->assertDatabaseHas('account_account_action', [
                'account_id' => $res->getData()->data->id,
                'account_action_id' => (int)trim($key, 'id '),
                'is_done' => (bool)$value,
            ]);
        }
        foreach ($data['gameInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_has_game_infos', [
                'account_id' => $res->getData()->data->id,
                'game_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }

        $intactData = $data;

        # Case: lack accountInfo
        $firstKeyAccountInfo = array_key_first($data['accountInfos']);
        unset($data['accountInfos'][$firstKeyAccountInfo]);
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountInfos.' . $firstKeyAccountInfo)
                ->etc()
        );

        # Case: lack accountAction
        $data = $intactData;
        $firstKeyAccountAction = array_key_first($data['accountActions']);
        unset($data['accountActions'][$firstKeyAccountAction]);
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountActions.' . $firstKeyAccountAction)
                ->etc()
        );

        # Case: invalid roleKey
        $data = $intactData;
        $data['roleKey'] = Str::random(10);
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($json) => $json
                ->has('errors.roleKey')
                ->etc()
        );
    }

    public function testMultipleStore()
    {
        for ($i  = 0; $i < 12; $i++) {
            $this->testStore();
        }
    }

    public function testUpdate()
    {
        $account = Account::inRandomOrder()
            ->whereIn('status_code', [0, 440])
            ->first();
        $creator = $account->creator;
        $creator->givePermissionTo('update_account');
        $creator->assignRole('tester');
        $creator->refresh();

        $accountType = $account->accountType;
        $route = route('account.update', ['account' => $account]);

        $data = [
            'roleKey' => 'tester',
            'username' => Str::random(60),
            'password' => Str::random(60),
            'cost' => rand(20000, 50000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'), UploadedFile::fake()->image('avatar4324.jpg')
            ],
            'accountInfos' =>  $this->makeDataForAccountInfos($accountType),
            'accountActions' => $this->makeDataForAccountActions($accountType),
            'gameInfos' => $this->makeDataForGameInfos($account->accountType->game),
            '_requiredModelRelationships' => ['representativeImage', 'otherImages']
        ];
        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('username', $data['username'])
                        ->where('password', $data['password'])
                        ->where('cost', $data['cost'])
                        ->where('description', $data['description'])
                        ->has('representativeImage.path')
                        ->has('otherImages.' . array_key_last($data['images']))
                        ->etc()
                )
        );
        foreach ($data['accountInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_account_info', [
                'account_id' => $res->getData()->data->id,
                'account_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }
        foreach ($data['accountActions'] as $key => $value) {
            $this->assertDatabaseHas('account_account_action', [
                'account_id' => $res->getData()->data->id,
                'account_action_id' => (int)trim($key, 'id '),
                'is_done' => (bool)$value,
            ]);
        }
        foreach ($data['gameInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_has_game_infos', [
                'account_id' => $res->getData()->data->id,
                'game_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }

        $intactData = $data;
        # Case: lack a part of accountInfo
        $firstKeyAccountInfo = array_key_first($data['accountInfos']);
        unset($data['accountInfos'][$firstKeyAccountInfo]);
        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountInfos.' . $firstKeyAccountInfo)
                ->etc()
        );

        # Case: lack lack a part of accountAction
        $data = $intactData;
        $firstKeyAccountAction = array_key_first($data['accountActions']);
        unset($data['accountActions'][$firstKeyAccountAction]);
        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountActions.' . $firstKeyAccountAction)
                ->etc()
        );

        # Case: invalid roleKey
        $data = $intactData;
        $data['roleKey'] = Str::random(10);
        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($json) => $json
                ->has('errors.roleKey')
                ->etc()
        );
    }

    public function testApprove()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 0)
            ->where('status_code', '<=', 99)
            ->first();
        $route = route('account.approve', ['account' => $account]);
        $user = User::inRandomOrder()->first();
        $user->givePermissionTo('approve_account');
        $user->refresh();

        $res = $this->actingAs($user)
            ->json('post', $route);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.statusCode', 480)
        );
    }

    public function testMultipleApprove()
    {
        foreach ([1, 2] as $nNnO) {
            $this->testApprove();
        }
    }

    public function test_resource()
    {
        $account = Account::where('buyer_id', '!=', null)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $buyer = $account->buyer;
        $buyer->syncPermissions();
        $buyer->syncRoles();
        $buyer->refresh();
        $user = User::whereNotIn('id', [$account->buyer->getKey(), $account->creator->getKey()])
            ->inRandomOrder()
            ->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();
        $data = [
            '_requiredModelRelationships' => ['accountActions', 'accountInfos'],
        ];

        # case as a manager
        $user->givePermissionTo('manage_account');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case as a buyer
        $res = $this->actingAs($buyer)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case as a creator
        $account = Account::whereIn('status_code', [0, 440])
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $creator = $account->creator;
        $creator->syncRoles();
        $creator->syncPermissions();
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case user can approve account
        $account = Account::where('status_code', '<=', 99)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $user->revokePermissionTo('manage_account');
        $user->givePermissionTo('approve_account');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case other user
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data')
                ->missing('data.accountActions')
                ->missing('data.accountInfos')
                ->missing('data.password')
        );
    }

    public function testStoreRouteMiddleware()
    {
        $game = Game::inRandomOrder()->first();
        $accountType = $game->accountTypes->random();
        $route = route('account.store', ['accountType' => $accountType]);

        /**
         * Is auth
         * ---------------------
         * create - can use account type
         */
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # 0 - 0
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # 0 - 1
        $user->assignRole('tester');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # 1 - 0
        $user->givePermissionTo('create_account');
        $user->removeRole('tester');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # 1 - 1
        $user->assignRole('tester');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }

    public function testApproveRouteMiddleware()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 0)
            ->where('status_code', '<=', 99)
            ->first();
        $invalidAccount = Account::inRandomOrder()
            ->where('status_code', '>=', 100)
            ->first();
        $route = route('account.approve', ['account' => $account]);
        $invalidRoute = route('account.approve', ['account' => $invalidAccount]);

        /**
         * Is auth
         * ---------------------
         * approve - account is valid
         */
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # 0 - 0
        $res = $this->actingAs($user)
            ->json('post', $invalidRoute);
        $res->assertStatus(403);

        # 0 - 1
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # 1 - 0
        $user->givePermissionTo('approve_account');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $invalidRoute);
        $res->assertStatus(403);

        # 1 - 1
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(200);
    }

    public function testUpdateRouteMiddle()
    {
        $validAccount = Account::inRandomOrder()
            ->whereIn('status_code', [0, 440])
            ->first();
        $validAccount->creator->syncPermissions();
        $validAccount->creator->syncRoles();
        $validAccount->creator->refresh();
        $validRoute = route('account.update', ['account' => $validAccount]);

        $validProAccount = Account::inRandomOrder()
            ->whereIn('status_code', [480])
            ->first();
        $validProAccount->creator->syncPermissions();
        $validProAccount->creator->syncRoles();
        $validProAccount->creator->refresh();
        $validProRoute = route('account.update', ['account' => $validProAccount]);

        $invalidAccount = Account::inRandomOrder()
            ->whereIn('status_code', [200, 600, 840, 880])
            ->first();
        $invalidAccount->creator->syncPermissions();
        $invalidAccount->creator->syncRoles();
        $invalidAccount->creator->refresh();
        $invalidRoute = route('account.update', ['account' => $invalidAccount]);

        /**
         * Regular user can't update
         * ---------------------------
         */
        $user = User::whereNotIn('id', [
            $validAccount->creator->getKey(),
            $validProAccount->creator->getKey(),
            $invalidAccount->creator->getKey(),
        ])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # Valid route
        $this->actingAs($user)
            ->json('put', $validRoute)
            ->assertStatus(403);

        # Valid pro route
        $this->actingAs($user)
            ->json('put', $validProRoute)
            ->assertStatus(403);

        # Invalid route
        $this->actingAs($user)
            ->json('put', $invalidRoute)
            ->assertStatus(403);

        /**
         * User can update
         * ---------------------------
         */
        $user->givePermissionTo('update_account');

        # Valid route
        $this->actingAs($user)
            ->json('put', $validRoute)
            ->assertStatus(403);

        # Valid pro route
        $this->actingAs($user)
            ->json('put', $validProRoute)
            ->assertStatus(403);

        # Invalid route
        $this->actingAs($user)
            ->json('put', $invalidRoute)
            ->assertStatus(403);

        /**
         * Creator can't update
         * ---------------------------
         */

        # Valid route
        $this->actingAs($validAccount->creator)
            ->json('put', $validRoute)
            ->assertStatus(403);

        # Valid pro route
        $this->actingAs($validProAccount->creator)
            ->json('put', $validProRoute)
            ->assertStatus(403);

        # Invalid route
        $this->actingAs($invalidAccount->creator)
            ->json('put', $invalidRoute)
            ->assertStatus(403);

        /**
         * Creator can update
         * ---------------------------
         */

        # Valid route
        $validAccount->creator->givePermissionTo('update_account');
        $validAccount->creator->refresh();
        $this->actingAs($validAccount->creator)
            ->json('put', $validRoute)
            ->assertStatus(422);

        # Valid pro route
        $validProAccount->creator->givePermissionTo('update_account');
        $validProAccount->creator->refresh();
        $this->actingAs($validProAccount->creator)
            ->json('put', $validProRoute)
            ->assertStatus(403);

        # Invalid route
        $invalidAccount->creator->givePermissionTo('update_account');
        $invalidAccount->creator->refresh();
        $this->actingAs($invalidAccount->creator)
            ->json('put', $invalidRoute)
            ->assertStatus(403);

        /**
         * Manager can update and mange
         * ---------------------------
         */
        $manager = $user;
        $manager->syncPermissions();
        $manager->givePermissionTo('update_account');
        $manager->givePermissionTo('manage_account');
        $manager->refresh();

        # Valid route
        $this->actingAs($manager)
            ->json('put', $validRoute)
            ->assertStatus(422);

        # Valid pro route
        $this->actingAs($manager)
            ->json('put', $validProRoute)
            ->assertStatus(422);

        # Invalid route
        $this->actingAs($manager)
            ->json('put', $invalidRoute)
            ->assertStatus(403);
    }
}
