<?php

namespace Tests\Feature\AccountType;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;

class StoreTest extends TestCase
{
    public function test_controller_and_request()
    {
        // Initial data
        $game = Game::inRandomOrder()->first();
        $route = route('account-type.store', ['game' => $game]);
        $data = [
            '_requiredModelRelationships' => ['rolesCanUsedAccountType']
        ];
        $this->actingAs($this->makeAuth([]));

        # Case: validate error
        $res = $this->json('post', $route, $data);
        $res->assertStatus(422);

        $data = array_merge($data, [
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
        ]);

        # Case: validate success
        $res = $this->json('post', $route, $data);
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
                                        ->where('pivot.statusCode', $data['rolesCanUsedAccountType'][0]['statusCode'])
                                        ->etc()
                                )
                                ->has(
                                    1,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][1]['key'])
                                        ->where('pivot.statusCode', $data['rolesCanUsedAccountType'][1]['statusCode'])
                                        ->etc()
                                )
                                ->has(
                                    2,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][2]['key'])
                                        ->where('pivot.statusCode', $data['rolesCanUsedAccountType'][2]['statusCode'])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
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
