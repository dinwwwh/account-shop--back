<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AccountType;
use App\Models\AccountInfo;
use App\Models\User;
use Illuminate\Support\Str;

class AccountInfoTest extends TestCase
{
    public function testStoreRouteMiddleware()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $user = User::factory()->make();
        $user->save();
        $user->revokePermissionTo('update_account_info', 'manage_account_info');
        $user->refresh();

        /**
         * Not auth
         * ----------
         */
        $res = $this->json('post', $route);
        $res->assertStatus(401);

        /**
         * Auth
         * ----------
         * Create
         */

        # Case: 0
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(403);

        # Case: 1
        $user->givePermissionTo('create_account_info');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }

    public function testUpdateRouteMiddleware()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $creator = $accountInfo->creator;
        $creator->revokePermissionTo('update_account_info', 'manage_account_info');
        $creator->refresh();
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $user = User::factory()->make();
        $user->save();
        $user->revokePermissionTo('update_account_info', 'manage_account_info');
        $user->refresh();

        /**
         * Not auth
         * ----------
         */
        $res = $this->json('put', $route);
        $res->assertStatus(401);

        /**
         * Auth
         * ----------
         * Update - Manage
         */


        # Case: 0 0 (as user)
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 0 0 (as creator)
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 1 0 (as user)
        $user->givePermissionTo('update_account_info');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 1 0 (as creator)
        $creator->givePermissionTo('update_account_info');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 1 1 (as user)
        $user->givePermissionTo('manage_account_info');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 1 1 (as creator)
        $creator->givePermissionTo('manage_account_info');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 0 1 (as user)
        $user->revokePermissionTo('update_account_info');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 0 1 (as creator)
        $creator->revokePermissionTo('update_account_info');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(403);
    }

    public function testStore()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $user = User::factory()->make();
        $user->save();
        $user->givePermissionTo('create_account_info');
        $user->refresh();
        $this->actingAs($user);
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
            'roleKeys' => ['administrator', 'customer'],
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('order', $data['order'])
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->has(
                            'rolesNeedFilling',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['roleKeys'][0])
                                        ->etc()
                                )
                                ->has(
                                    1,
                                    fn ($json) => $json
                                        ->where('key', $data['roleKeys'][1])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
        );
    }

    public function testShow()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $route = route('account-info.show', ['accountInfo' => $accountInfo]);
        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('id', $accountInfo->id)
                        ->where('order', $accountInfo->order)
                        ->where('name', $accountInfo->name)
                        ->where('slug', $accountInfo->slug)
                        ->where('description', $accountInfo->description)
                        ->has('rule')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('updatedAt')
                        ->has('createdAt')
                        ->has('pivot')
                        ->has('rolesNeedFilling')
                )
        );
    }

    public function testUpdate()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $creator = $accountInfo->creator;
        $creator->givePermissionTo('update_account_info');
        $creator->refresh();
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
            'roleKeys' => ['administrator', 'customer', 'tester'],
        ];

        $this->actingAs($creator);
        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('order', $data['order'])
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->has('rolesNeedFilling.2.key')
                        ->etc()
                )
        );
    }
}
