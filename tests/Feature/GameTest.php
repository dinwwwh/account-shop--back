<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\UploadedFile;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GameTest extends TestCase
{
    public function testCreate()
    {
        $user = User::factory()->make();
        $user->save();

        // Case: don't have power to create a game
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(403);


        // Empower
        $user->givePermissionTo('create_game');
        $user->refresh();


        // case: error validate
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(422);

        // Case: success
        $data = [
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        $res = $this->actingAs($user)
            ->json('post', route('game.store'), $data);

        // Asserts
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->where('publisherName', $data['publisherName'])
                    ->etc()
            )

        );
    }

    public function testRead()
    {
        $game = Game::first();
        $res = $this->json('get', route('game.show', ['game' => $game]));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json->where('id', $game->id)
                    ->where('name', $game->name)
                    ->etc()
            )
        );
    }

    public function testUpdate()
    {
        $game = Game::first();
        $user = User::factory()->make();
        $user->save();

        // Don't have power
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        // Empower
        $user->givePermissionTo('update_game');
        $user->givePermissionTo('manage_game');
        $user->refresh();

        $data = [
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
        ];
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->where('publisherName', $data['publisherName'])
                    ->etc()
            )
        );

        $user = $game->creator;
        $user->revokePermissionTo('update_game');
        $user->refresh();

        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]), $data);
        $res->assertStatus(403);

        $user->givePermissionTo('update_game');
        $user->refresh();

        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]), $data);
        $res->assertStatus(200);
    }

    // public function testDelete()
    // {
    // }
}
