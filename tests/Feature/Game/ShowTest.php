<?php

namespace Tests\Feature\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    public function test_controller_and_request()
    {
        $game = Game::inRandomOrder()->first();
        $res = $this->json('get', route('game.show', ['game' => $game]));
        $res->assertStatus(200);
    }
}
