<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use App\Models\AccountType;
use Illuminate\Support\Str;

class AccountTypeTest extends TestCase
{
    public function testStoreRouteMiddleware()
    {
        /**
         * Test route store
         * ---------------
         * auth - create
         */
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();
        $route = route('account-type.store', ['game' => Game::inRandomOrder()->first()]);

        # Case: 0 0
        $res = $this->json('post', $route);
        $res->assertStatus(401);

        # Case: 1 0
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # Case: 1 1
        $user->givePermissionTo('create_account_type');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }

    public function testUpdateRouteMiddleware()
    {
        // /**
        //  * Test route update
        //  * ----------------------
        //  * auth - update - manage
        //  */
        $accountType = AccountType::inRandomOrder()->first();
        $creator = $accountType->creator;
        $creator->revokePermissionTo('update_account_type', 'manage_account_type');
        $creator->refresh();
        $user = User::whereNotIn('id', [$creator->getKey()])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # Case: 0 0 0
        $res = $this->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(401);

        # Case: 1 0 0 (as user)
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        # Case: 1 0 0 (as creator)
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        # Case: 1 1 0 (as user)
        $user->givePermissionTo('update_account_type');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        # Case: 1 1 0 (as creator)
        $creator->givePermissionTo('update_account_type');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(200);

        # Case: 1 1 1 (as user)
        $user->givePermissionTo('update_account_type', 'manage_account_type');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(200);

        # Case: 1 1 1 (as creator)
        $creator->givePermissionTo('mange_account_type');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(200);

        # Case: 1 0 1 (as user)
        $user->revokePermissionTo('update_account_type');
        $user->givePermissionTo('manage_account_type');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        # Case: 1 0 1 (as creator)
        $creator->revokePermissionTo('update_account_type');
        $creator->givePermissionTo('manage_account_type');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);
    }

    public function testStore()
    {
        // Initial data
        $user = User::inRandomOrder()->first();
        $user->givePermissionTo('create_account_type');
        $user->refresh();
        $game = Game::inRandomOrder()->first();
        $route = route('account-type.store', ['game' => $game]);
        $data = [
            'gameId' => $game->id,
            'name' => Str::random(20),
            'description' => Str::random(20),
            'rolesCanUsedAccountType' => [
                [
                    'key' => 'administrator',
                    'statusCode' => 0,
                ],
                [
                    'key' => 'customer',
                    'statusCode' => 440,
                ],
                [
                    'key' => 'tester',
                    'statusCode' => 440,
                ],
            ],
        ];

        # Case: validate error
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);

        # Case: validate success
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->has(
                            'rolesCanUsedAccountType',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][0]['key'])
                                        ->where('pivot.status_code', $data['rolesCanUsedAccountType'][0]['statusCode'])
                                        ->etc()
                                )
                                ->has(
                                    1,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][1]['key'])
                                        ->where('pivot.status_code', $data['rolesCanUsedAccountType'][1]['statusCode'])
                                        ->etc()
                                )
                                ->has(
                                    2,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][2]['key'])
                                        ->where('pivot.status_code', $data['rolesCanUsedAccountType'][2]['statusCode'])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
        );
    }

    public function testRead()
    {
        // Initial data
        $accountType = AccountType::inRandomOrder()->first();

        /**
         * Have power
         * -----------------------------
         */
        $res = $this->json('get', route('account-type.show', ['accountType' => $accountType]));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('id', $accountType->id)
                        ->where('name', $accountType->name)
                        ->where('slug', $accountType->slug)
                        ->where('description', $accountType->description)
                        ->has('accountInfos')
                        ->has('accountActions')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('updatedAt')
                        ->has('createdAt')
                        ->has('rolesCanUsedAccountType')
                )
        );
    }

    public function testUpdate()
    {
        // Initial data
        $accountType = AccountType::inRandomOrder()->first();
        $creator = $accountType->creator;
        $creator->givePermissionTo('update_account_type');
        $creator->refresh();

        $data = [
            'name' => Str::random(20),
            'description' => Str::random(20),
            'rolesCanUsedAccountType' => [
                [
                    'key' => 'administrator',
                    'statusCode' => 200
                ]
            ],
        ];
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->has(
                            'rolesCanUsedAccountType',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][0]['key'])
                                        ->where('pivot.status_code', $data['rolesCanUsedAccountType'][0]['statusCode'])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
        );
    }

    // public function testDelete()
    // {
    // }
}
