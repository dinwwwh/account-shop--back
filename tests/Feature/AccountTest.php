<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountAction;
use App\Models\AccountInfo;
use App\Models\AccountType;
use App\Models\Rule;
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
        $accountInfos = $accountType->accountInfosThatRoleNeedFilling(Role::find('tester'));
        $data = [];

        foreach ($accountInfos as $accountInfo) {
            if ($accountInfo->rule->required) {
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
        $accountActions = $accountType->accountActionsThatRoleNeedPerforming(Role::find('tester'));
        $data = [];

        foreach ($accountActions as $accountAction) {
            if ($accountAction->required) {
                $data['id' . $accountAction->getKey()] = true;
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
                        ->has('representativeImagePath')
                        ->has('images.' . array_key_last($data['images']))
                        ->has('infos.' . (count($data['accountInfos']) - 1))
                        ->has('actions.' . (count($data['accountActions']) - 1))
                        ->etc()
                )
        );

        $accountType = $game->accountTypes->random();
        $route = route('account.store', ['accountType' => $accountType]);
        $dataOfAccountActions = $this->makeDataForAccountActions($accountType);
        $dataOfAccountInfos = $this->makeDataForAccountInfos($accountType);
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
        ];
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
                        ->has('representativeImagePath')
                        ->has('images.' . array_key_last($data['images']))
                        ->has('infos.' . (count($data['accountInfos']) - 1))
                        ->has('actions.' . (count($data['accountActions']) - 1))
                        ->etc()
                )
        );
        $intactData = $data;

        # Case: lack accountInfo
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

        # Case: lack accountAction
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

    public function testShow()
    {
        $account = Account::inRandomOrder()->first();

        $route = route('account.show', ['account' => $account]);

        $res = $this->json('get', $route);

        $res->assertStatus(200);

        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('id', $account->id)
                        ->where('username', $account->username)
                        ->where('password', $account->password)
                        ->where('cost', $account->cost)
                        ->where('price', $account->calculateTemporaryPrice())
                        ->where('statusCode', $account->status_code)
                        ->where('description', $account->description)
                        ->has('representativeImagePath')
                        ->has('lastRoleKeyCreatorUsed')
                        ->has('images')
                        ->has('game')
                        ->has('accountType')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('censor')
                        ->has('type')
                        ->has('infos')
                        ->has('actions')
                        ->has('approvedAt')
                        ->has('updatedAt')
                        ->has('createdAt')
                )
        );
    }

    public function testStoreRouteMiddleware()
    {
        $game = Game::inRandomOrder()->first();
        $accountType = $game->accountTypes->random();
        $route = route('account.store', ['accountType' => $accountType]);

        /**
         * Not auth
         * -------------------
         */
        $res = $this->json('post', $route);
        $res->assertStatus(401);

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
         * Not auth
         * -------------------
         */
        $res = $this->json('post', $route);
        $res->assertStatus(401);

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
         * Not Auth
         * ---------------------------
         */

        # Valid route
        $this->json('put', $validRoute)
            ->assertStatus(401);

        # Valid pro route
        $this->json('put', $validProRoute)
            ->assertStatus(401);

        # Invalid route
        $this->json('put', $invalidRoute)
            ->assertStatus(401);

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
