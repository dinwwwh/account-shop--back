<?php

namespace Tests\Feature\GameInfo;

use App\Models\GameInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReadTest extends TestCase
{
    public function test_controller_and_request()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.show', ['gameInfo' => $gameInfo]);

        $res = $this->json('get', $route);

        $res->assertStatus(200);
    }
}
