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
    public function test_controller_and_request()
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
                'required' => null,
                'requiredRoleKeys' => ['tester'],
            ],
            '_requiredModelRelationships' => ['rule']
        ];
        $res = $this->json('post', $route, $data);
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
            '_requiredModelRelationships' => ['rule'],
        ];
        $res = $this->json('post', $route, $data);
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
