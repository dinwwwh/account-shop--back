<?php

namespace Tests\Feature\Game;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        // Initial data
        $this->actingAs($this->makeAuth([]));
        $data = [
            'order' => 1,
            'publisherName' => Str::random(10),
            'name' => Str::random(10),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
            '_requiredModelRelationships' => ['representativeImage'],
        ];

        $res = $this->json('post', route('game.store'), $data);
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

    /**
     * Case is manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $route = route('game.store');
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is manager but lack create_game
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['create_game']);
        $this->actingAs($user);
        $route = route('game.store');
        $this->json('post', $route)->assertStatus(403);
    }
}
