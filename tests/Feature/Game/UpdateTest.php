<?php

namespace Tests\Feature\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Str;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function test_controller_and_request()
    {
        // Initial data
        $game = Game::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([]));
        $data = [
            'order' => rand(1, 100),
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            '_requiredModelRelationships' => ['representativeImage'],
        ];

        $res = $this->json('put', route('game.update', ['game' => $game]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->where('publisherName', $data['publisherName'])
                    ->where('order', $data['order'])
                    ->where('name', $data['name'])
                    ->has('representativeImage.path')
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
        $route = route('game.update', ['game' => $game]);
        $this->json('put', $route)->assertStatus(200);
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
        $route = route('game.update', ['game' => $game]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $game = Game::inRandomOrder()->first();
        $route = route('game.update', ['game' => $game]);
        $this->json('put', $route)->assertStatus(403);
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
        $route = route('game.update', ['game' => $game]);
        $this->json('put', $route)->assertStatus(403);
    }
}
