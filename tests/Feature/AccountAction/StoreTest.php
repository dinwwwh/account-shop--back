<?php

namespace Tests\Feature\AccountAction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountType;
use Str;
use Arr;

class StoreTest extends TestCase
{
    public function test_controller_and_request()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);

        # Case required is null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => null,
            'requiredRoleKeys' => ['administrator', 'customer'],
            '_requiredModelRelationships' => ['requiredRoles']
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('order', $data['order'])
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->has(
                            'requiredRoles',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['requiredRoleKeys'][0])
                                        ->etc()
                                )
                                ->has(
                                    1,
                                    fn ($json) => $json
                                        ->where('key', $data['requiredRoleKeys'][1])
                                        ->etc()
                                )
                        )
                        ->etc()
                )
        );

        # Case required isn't null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => Arr::random([true, false]),
            '_requiredModelRelationships' => ['requiredRoles'],
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('order', $data['order'])
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->where('videoPath', $data['videoPath'])
                        ->where('required', $data['required'])
                        ->where('requiredRoles', [])
                        ->etc()
                )
        );
    }

    /**
     * Case if manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is manager but lack update_game
     */
    public function test_middleware_fail_1()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], [$accountType->game->creator]);
        $this->actingAs($user);
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }

    /**
     * Case is creator of game but lack update_game permission
     */
    public function test_middleware_fail_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }
}
