<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Arr;

class GameInfoTest extends TestCase
{
    public function test_store()
    {
        $game = Game::inRandomOrder()->first();
        $route = route('game-info.store', ['game' => $game]);
        $user = User::inRandomOrder()->first();
        $user->assignRole('administrator');
        $user->refresh();

        # Case rule's required is null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => null,
                'requiredRoleKeys' => ['tester'],
            ],
            '_with' => ['rule']
        ];
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.order', $data['order'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.rule.required', $data['rule']['required'])
                ->where('data.rule.requiredRoles.0.key', $data['rule']['requiredRoleKeys'][0])
        );

        # Case rule's required isn't null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => Arr::random([true, false]),
            ],
            '_with' => ['rule'],
        ];
        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.order', $data['order'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.rule.required', $data['rule']['required'])
                ->where('data.rule.requiredRoles', [])
        );
    }

    public function test_show()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.show', ['gameInfo' => $gameInfo]);

        $res = $this->json('get', $route);

        $res->assertStatus(200);
        // $res->assertJson(
        //     fn ($j) => $j
        //         ->has(
        //             'data',
        //             fn ($j) => $j
        //                 ->where('id', $gameInfo->getKey())
        //                 ->where('order', $gameInfo->order)
        //                 ->where('name', $gameInfo->name)
        //                 ->where('slug', $gameInfo->slug)
        //                 ->where('description', $gameInfo->description)
        //                 ->has('rule')
        //                 ->has('lastUpdatedEditor')
        //                 ->has('creator')
        //                 ->has('createdAt')
        //                 ->has('updatedAt')
        //                 ->has('pivot')
        //         )
        // );
    }

    public function test_update()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $user = User::inRandomOrder()->first();
        $user->assignRole('administrator');
        $user->refresh();

        # Case rule's required is null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => null,
                'requiredRoleKeys' => ['tester'],
            ],
            '_with' => ['rule.requiredRoles']
        ];
        $res = $this->actingAs($user)
            ->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.order', $data['order'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.rule.required', $data['rule']['required'])
                ->where('data.rule.requiredRoles.0.key', $data['rule']['requiredRoleKeys'][0])
        );

        // # Case rule's required isn't null
        // $data = [
        //     'order' => rand(1, 5),
        //     'name' => Str::random(40),
        //     'description' => Str::random(80),
        //     'rule' => [
        //         'required' => Arr::random([true, false]),
        //         'requiredRoleKeys' => ['tester'],
        //     ],
        //     '_with' => ['rule.requiredRoles']
        // ];
        // $res = $this->actingAs($user)
        //     ->json('put', $route, $data);

        // $res->assertStatus(200);
        // $res->assertJson(
        //     fn ($j) => $j
        //         ->where('data.order', $data['order'])
        //         ->where('data.name', $data['name'])
        //         ->where('data.description', $data['description'])
        //         ->where('data.rule.required', $data['rule']['required'])
        //         ->where('data.rule.requiredRoles', [])
        // );
    }

    public function test_destroy()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $user = User::inRandomOrder()->first();
        $user->assignRole('administrator');
        $user->refresh();

        $gameInfoKey = $gameInfo->getKey();
        $ruleKey = $gameInfo->rule->getKey();

        $this->actingAs($user)
            ->json('delete', $route);

        $this->assertDatabaseMissing('game_infos', [
            'id' => $gameInfoKey,
            'deleted_at' => null
        ]);
        $this->assertDatabaseMissing('rules', [
            'id' => $ruleKey
        ]);
    }


    public function test_routeStoreMiddleware()
    {
        $game = Game::inRandomOrder()->first();
        $route = route('game-info.store', ['game' => $game]);
        $user = User::inRandomOrder()->first();
        $user->syncRoles();
        $user->syncPermissions();
        $user->refresh();

        /**
         * Auth
         * -----------
         * update game - create game info
         */

        #Case: 0  - 0 (fail)
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        #Case: 0  - 1 (fail)
        $user->givePermissionTo('create_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        #Case: 1  - 0 (fail)
        $user->revokePermissionTo('create_game_info');
        $user->givePermissionTo('update_game', 'manage_game');
        $user->refresh();
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        #Case: 1  - 1 (success)
        $user->givePermissionTo('create_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(422);
    }

    public function test_routeUpdateMiddleware()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $user = User::inRandomOrder()->first();
        $user->syncRoles();
        $user->syncPermissions();
        $user->refresh();

        /**
         * Auth
         * -----------
         * update game - update game info
         */

        #Case: 0  - 0 (fail)
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);

        #Case: 0  - 1 (fail)
        $user->givePermissionTo('update_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);

        #Case: 1  - 0 (fail)
        $user->revokePermissionTo('update_game_info');
        $user->givePermissionTo('update_game', 'manage_game');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);

        #Case: 1  - 1 (success)
        $user->givePermissionTo('update_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(200);
    }

    public function test_routeDestroyMiddleware()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);

        /**
         * Auth
         * -----------
         * update game - destroy game info
         */
        $user = User::inRandomOrder()->first();
        $user->syncRoles();
        $user->syncPermissions();
        $user->refresh();

        #Case: 0  - 0 (fail)
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        #Case: 0  - 1 (fail)
        $user->givePermissionTo('delete_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        #Case: 1  - 0 (fail)
        $user->revokePermissionTo('delete_game_info');
        $user->givePermissionTo('update_game', 'manage_game');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        #Case: 1  - 1 (success)
        $user->givePermissionTo('delete_game_info');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(200);
    }
}
