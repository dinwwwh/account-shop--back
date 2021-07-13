<?php

namespace Tests\Feature\GameInfo;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;
use Arr;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        $game = Game::inRandomOrder()->first();
        $route = route('game-info.store', ['game' => $game]);
        $this->actingAs($this->makeAuth([]));

        # Case rule's required is null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => true,
                'requiredUserIds' => [1, 2, 3],
                'unrequiredUserIds' => [4, 5, 6, 7],
            ],
            '_requiredModelRelationships' => ['rule']
        ];
        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJsonCount(0, 'data.rule.requiredUsers');
        $res->assertJsonCount(4, 'data.rule.unrequiredUsers');
        $res->assertJson(
            fn ($j) => $j
                ->where('data.rule.required', true)
        );
        $ruleId = $res->getData()->data->rule->id;

        $this->assertDatabaseHas('game_infos', [
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        foreach ($data['rule']['requiredUserIds'] as $userId) {
            $this->assertDatabaseMissing('rule_user_required', [
                'user_id' => $userId,
                'rule_id' => $ruleId,
            ]);
        }

        foreach ($data['rule']['unrequiredUserIds'] as $userId) {
            $this->assertDatabaseHas('rule_user_unrequired', [
                'user_id' => $userId,
                'rule_id' => $ruleId,
            ]);
        }
    }

    /**
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $game = Game::inRandomOrder()->first();
        $route = route('game-info.store', ['game' => $game]);
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
        $route = route('game-info.store', ['game' => $game]);
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
        $route = route('game-info.store', ['game' => $game]);
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
        $route = route('game-info.store', ['game' => $game]);
        $this->json('post', $route)->assertStatus(403);
    }
}
