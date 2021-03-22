<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\UploadedFile;
use Storage;
use App\Models\Game;

class GameTest extends TestCase
{
    /**
     * test route 'game.store'
     *
     * @return void
     */
    public function testStore()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $gameData = [
            'name' => 'goi rong online',
            'image' => $file,
            'publisherName' => 'sohagame',
        ];

        $response = $this->json('POST', route('game.store'), $gameData);

        $response->assertStatus(201);
        $response->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->where('name', $gameData['name'])
                    ->where('publisherName', $gameData['publisherName'])
                    ->etc()
            )
        );

        $this->game = Game::find($response->getData()->data->id);
    }

    /**
     * test route 'game.index'
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('GET', route('game.index'));

        $response->assertStatus(200);
        $response->assertJson(fn ($json) => $json->has('data'));
    }

    /**
     * test route 'game.show'
     *
     * @return void
     */
    public function testShow()
    {
        $game = Game::first();

        // Case success
        $response = $this->json('GET', route('game.show', ['game' => $game]));
        $response->assertStatus(200);

        // Case game id not found
        $response = $this->json('GET', route('game.show', ['game' => -1]));
        $response->assertStatus(404);
    }
}
