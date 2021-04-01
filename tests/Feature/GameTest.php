<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\UploadedFile;
use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GameTest extends TestCase
{
    public function testCreate()
    {
        // case: error validate
        $res = $this->json('post', route('game.store'));
        $res->assertStatus(403);

        // Case: success
        $data = [
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        $res = $this->json('post', route('game.store'), $data);

        // Asserts
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->where('publisherName', $data['publisherName'])
                ->etc()
        );
    }

    public function testRead()
    {
        $game = Game::first();
        $res = $this->json('get', route('game.show', ['game' => $game]));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('id', $game->id)
                ->where('name', $game->name)
                ->etc()
        );
    }

    public function testUpdate()
    {
        $game = Game::first();
        $data = [
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            // 'image' => UploadedFile::fake()->image('image_one.jpg'),
        ];
        $res = $this->json('put', route('game.update', ['game' => $game]), $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->where('publisherName', $data['publisherName'])
                ->etc()
        );
    }

    public function testDelete()
    {
    }
}
