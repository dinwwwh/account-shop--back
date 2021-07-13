<?php

namespace Tests\Feature\AccountType;

use App\Models\Game;
use App\Models\User;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        // Initial data
        $game = Game::inRandomOrder()->first();
        $route = route('account-type.store', ['game' => $game]);
        $data = [
            '_requiredModelRelationships' => ['usableUsers', 'approvableUsers']
        ];
        $this->actingAs($this->makeAuth([]));

        # Case: validate error
        $res = $this->json('post', $route, $data);
        $res->assertStatus(422);

        $data = array_merge($data, [
            'gameId' => $game->id,
            'name' => Str::random(20),
            'description' => Str::random(20),
        ]);

        $usableUsers = User::inRandomOrder()->limit(3)->get();
        foreach ($usableUsers as $user) {
            $data['usableUsers'][] = [
                'id' => $user->getKey(),
                'statusCode' => Arr::random([0, 440, 480]),
            ];
        };

        $approvableUsers = User::inRandomOrder()->limit(2)->get();
        foreach ($approvableUsers as $user) {
            $data['approvableUsers'][] = [
                'id' => $user->getKey(),
                'statusCode' => Arr::random([440, 480]),
            ];
        };

        # Case: validate success
        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJsonCount(count($data['usableUsers']), 'data.usableUsers');
        $res->assertJsonCount(count($data['approvableUsers']), 'data.approvableUsers');
        $accountTypeId = $res->getData()->data->id;

        foreach ($data['usableUsers'] as $user) {
            $this->assertDatabaseHas('account_type_user_usable', [
                'user_id' => $user['id'],
                'account_type_id' => $accountTypeId,
                'status_code' => $user['statusCode'],
            ]);
        };

        foreach ($data['approvableUsers'] as $user) {
            $this->assertDatabaseHas('account_type_user_approvable', [
                'user_id' => $user['id'],
                'account_type_id' => $accountTypeId,
                'status_code' => $user['statusCode'],
            ]);
        };
    }

    /**
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $game = Game::inRandomOrder()->first();
        $route = route('account-type.store', ['game' => $game]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $game = Game::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $game->creator);
        $this->actingAs($user);
        $route = route('account-type.store', ['game' => $game]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $game = Game::inRandomOrder()->first();
        $route = route('account-type.store', ['game' => $game]);
        $this->json('post', $route)->assertStatus(403);
    }

    /**
     * Case is creator of game but lack update_game permission
     */
    public function test_middleware_fail_2()
    {
        $game = Game::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $game->creator);
        $this->actingAs($user);
        $route = route('account-type.store', ['game' => $game]);
        $this->json('post', $route)->assertStatus(403);
    }
}
