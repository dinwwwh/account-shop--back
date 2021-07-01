<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountAction;
use App\Models\AccountType;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class AccountActionTest extends TestCase
{
    public function testStore()
    {
        $user = User::inRandomOrder()->first();
        $user->save();
        $user->givePermissionTo('create_account_action');
        $user->refresh();
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);

        # Case required is null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => null,
            'requiredRoleKeys' => ['administrator', 'customer'],
            '_with' => ['requiredRoles']
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
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->has(
                            'requiredRoles',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['requiredRoleKeys'][0])
                                        ->etc()
                                )
                                ->has(
                                    1,
                                    fn ($json) => $json
                                        ->where('key', $data['requiredRoleKeys'][1])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
        );

        # Case required isn't null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => Arr::random([true, false]),
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
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->where('requiredRoles', [])
                        ->etc()
                )
        );
    }
    public function testShow()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $route = route('account-action.show', ['accountAction' => $accountAction]);
        $res = $this->json('get', $route);
        $res->assertStatus(200);
        // $res->assertJson(
        //     fn ($json) => $json
        //         ->has(
        //             'data',
        //             fn ($json) => $json
        //                 ->where('id', $accountAction->id)
        //                 ->where('order', $accountAction->order)
        //                 ->where('name', $accountAction->name)
        //                 ->where('slug', $accountAction->slug)
        //                 ->where('description', $accountAction->description)
        //                 ->where('videoPath', $accountAction->video_path)
        //                 ->where('required', $accountAction->required)
        //                 ->has('requiredRoles')
        //                 ->has('creator')
        //                 ->has('lastUpdatedEditor')
        //                 ->has('updatedAt')
        //                 ->has('createdAt')
        //                 ->has('pivot')
        //         )
        // );
    }
    public function testUpdate()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $creator = $accountAction->creator;
        $creator->givePermissionTo('update_account_action');
        $creator->refresh();
        $this->actingAs($creator);
        $route = route('account-action.update', ['accountAction' => $accountAction]);

        # Case required is null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => null,
            'requiredRoleKeys' => ['administrator', 'customer', 'tester'],
            '_with' => ['requiredRoles'],
        ];

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
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->has('requiredRoles.2.key')
                        ->etc()
                )
        );

        # Case required isn't null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => Arr::random([true, false]),
        ];

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
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->where('requiredRoles', [])
                        ->etc()
                )
        );
    }
    public function testStoreRouteMiddleware()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

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
        $user->givePermissionTo('create_account_action');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }
    public function testUpdateRouteMiddleware()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $creator = $accountAction->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $user = User::whereNotIn('id', [$creator->getKey()])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

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
        $user->givePermissionTo('update_account_action');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 1 0 (as creator)
        $creator->givePermissionTo('update_account_action');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 1 1 (as user)
        $user->givePermissionTo('manage_account_action');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 1 1 (as creator)
        $creator->givePermissionTo('manage_account_action');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(200);

        # Case: 0 1 (as user)
        $user->revokePermissionTo('update_account_action');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', $route);
        $res->assertStatus(403);

        # Case: 0 1 (as creator)
        $creator->revokePermissionTo('update_account_action');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', $route);
        $res->assertStatus(403);
    }
}
