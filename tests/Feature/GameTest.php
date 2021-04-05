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

    public function testMiddleware()
    {
        $user = User::factory()->make();
        $user->save();

        /**
         * Route game.store
         * -------------------------------
         * auth - create
         */
        $user->revokePermissionTo('create_game');
        $user->refresh();

        # Case: 0 0
        $res = $this->json('post', route('game.store'));
        $res->assertStatus(401);

        # Case: 1 0
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(403);

        # Case: 1 1 (but validate error)
        $user->givePermissionTo('create_game');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('post', route('game.store'));
        $res->assertStatus(422);


        /**
         * Route game.update
         * -------------------------------
         * auth - update - mange
         */
        $game = Game::first();
        $creator = $game->creator;
        $creator->revokePermissionTo('update_game', 'manage_game');
        $creator->refresh();
        $user->revokePermissionTo('update_game', 'manage_game');
        $user->refresh();

        # Case: 0 0 0
        $res = $this->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        # Case: 1 0 0 (as manager)
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        # Case: 1 0 0 (as creator)
        $res = $this->actingAs($creator)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        # Case: 1 1 0 (as manager)
        $user->revokePermissionTo('mange_game');
        $user->givePermissionTo('update_game');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        # Case: 1 1 0 (as creator)
        $creator->revokePermissionTo('mange_game');
        $creator->givePermissionTo('update_game');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(200);

        # Case: 1 1 1 (as manager)
        $user->revokePermissionTo();
        $user->givePermissionTo('update_game', 'manage_game');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(200);

        # Case: 1 1 1 (as creator)
        $creator->givePermissionTo('update_game', 'manage_game');
        $creator->revokePermissionTo();
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(200);

        # Case: 1 0 1 (as manager)
        $user->revokePermissionTo('update_game');
        $user->givePermissionTo('manage_game');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);

        # Case: 1 0 1 (as creator)
        $creator->revokePermissionTo('update_game');
        $creator->givePermissionTo('manage_game');
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('put', route('game.update', ['game' => $game]));
        $res->assertStatus(403);
    }


    public function testCreate()
    {
        // Initial data
        $user = User::factory()->make();
        $user->save();
        $user->givePermissionTo('create_game');
        $user->refresh();
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
                    ->where('name', $data['name'])
                    ->has('imagePath')
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
                    ->has('accountTypesThatCurrentUserCanUse')
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
