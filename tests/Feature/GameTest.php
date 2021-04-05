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
        // Initial data
        $user = User::factory()->make();
        $user->save();

        /**
         * Don't have power to create a game - no logged
         * ---------------------------------
         */
        $res = $this->json('post', route('game.store'));
        $res->assertStatus(401);

        /**
         * Don't have power to create a game - logged
         * ---------------------------------
         */

        # Case: don't have power to create a game
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(403);



        $user->givePermissionTo('create_game');
        $user->refresh();
        /**
         * Had power to create a game
         * ---------------------------------
         */

        # Case: error validate
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(422);

        # Case: success validate
        $data = [
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];
        $res = $this->actingAs($user)
            ->json('post', route('game.store'), $data);
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
        /**
         * Had power to read sensitive info
         * ---------------------------------
         */
        $game = Game::first();
        $res = $this->json('get', route('game.show', ['game' => $game]));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->where('id', $game->id)
                    ->where('order', $game->order)
                    ->where('name', $game->name)
                    ->where('name', $game->name)
                    ->where('slug', $game->slug)
                    ->where('publisherName', $game->publisher_name)
                    ->has('imagePath')
                    ->has('currentRoleCanUsedAccountTypes')
                    ->has('accountTypes')
                    ->has('lastUpdatedEditor')
                    ->has('creator')
                    ->has('updatedAt')
                    ->has('createdAt')
            )
        );
    }

    public function testUpdate()
    {
        // Initial data
        $game = Game::first();
        $creator = $game->creator;
        $user = User::factory()->make();
        $user->save();

        /**
         * Don't have power to update - no logged
         * ---------------------------------
         */
        $res = $this->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(401);

        /**
         * Don't have power to update - user
         * ---------------------------------
         */

        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        /**
         * Don't have power to update - creator
         * ---------------------------------
         */
        $creator->revokePermissionTo('update_game');
        $creator->refresh();

        $res = $this->actingAs($creator)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        /**
         * Have power to read sensitive info -- user
         * ---------------------------------
         */
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

        /**
         * Have power to read sensitive info -- creator
         * ---------------------------------
         */
        $creator->revokePermissionTo('update_game');
        $creator->givePermissionTo('update_game');
        $creator->refresh();

        $res = $this->actingAs($creator)
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
    }

    // public function testDelete()
    // {
    // }
}
