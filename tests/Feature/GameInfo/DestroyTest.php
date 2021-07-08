<?php

namespace Tests\Feature\GameInfo;

use App\Models\GameInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    public function test_controller_and_request()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $this->actingAs($this->makeAuth([]));

        $gameInfoKey = $gameInfo->getKey();
        $ruleKey = $gameInfo->rule->getKey();

        $this->json('delete', $route);

        $this->assertDatabaseMissing('game_infos', [
            'id' => $gameInfoKey,
            'deleted_at' => null
        ]);
        $this->assertDatabaseMissing('rules', [
            'id' => $ruleKey
        ]);
    }

    /**
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $this->json('delete', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $gameInfo = GameInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $gameInfo->game->creator);
        $this->actingAs($user);
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $this->json('delete', $route)->assertStatus(200);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $this->json('delete', $route)->assertStatus(403);
    }

    /**
     * Case is creator of game but lack update_game permission
     */
    public function test_middleware_fail_2()
    {
        $gameInfo = GameInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $gameInfo->game->creator);
        $this->actingAs($user);
        $route = route('game-info.destroy', ['gameInfo' => $gameInfo]);
        $this->json('delete', $route)->assertStatus(403);
    }
}
