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
        $user = User::inRandomOrder()->first();
        $user->givePermissionTo('create_game');
        $user->refresh();
        $data = [
            'order' => 1,
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            '_requiredModelRelationships' => ['representativeImage'],
        ];

        $res = $this->actingAs($user)
            ->json('post', route('game.store'), $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json->has(
                'data',
                fn ($json) => $json
                    ->has('id')
                    ->where('order', $data['order'])
                    ->where('publisherName', $data['publisherName'])
                    ->where('name', $data['name'])
                    ->has('representativeImage.path')
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
        $game = Game::inRandomOrder()->first();
        $res = $this->json('get', route('game.show', ['game' => $game]));
        $res->assertStatus(200);
        // $res->assertJson(
        //     fn ($json) => $json->has(
        //         'data',
        //         fn ($json) => $json
        //             ->where('id', $game->id)
        //             ->where('order', $game->order)
        //             ->where('name', $game->name)
        //             ->where('name', $game->name)
        //             ->where('slug', $game->slug)
        //             ->where('publisherName', $game->publisher_name)
        //             ->where('description', $game->description)
        //             ->has('imagePath')
        //             ->has('accountTypesThatCurrentUserCanUse')
        //             ->has('accountTypes')
        //             ->has('lastUpdatedEditor')
        //             ->has('creator')
        //             ->has('updatedAt')
        //             ->has('createdAt')
        //             ->has('rolesCanCreatedGame')
        //             ->has('gameInfos')
        //     )
        // );
    }

    public function testUpdate()
    {
        // Initial data
        $game = Game::inRandomOrder()->first();
        $creator = $game->creator;
        $data = [
            'order' => rand(1, 100),
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            '_requiredModelRelationships' => ['representativeImage'],
        ];

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
                    ->where('order', $data['order'])
                    ->where('name', $data['name'])
                    ->has('representativeImage.path')
                    ->etc()
            )
        );
    }

    public function testStoreRouteMiddleware()
    {
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        /**
         * Route game.store
         * -------------------------------
         * auth - create
         */

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
    }

    public function testUpdateRouteMiddleware()
    {
        /**
         * Route game.update
         * -------------------------------
         * auth - update - mange
         */
        $game = Game::inRandomOrder()->first();
        $creator = $game->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();
        $user = User::whereNotIn('id', [$creator->getKey()])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

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
}
