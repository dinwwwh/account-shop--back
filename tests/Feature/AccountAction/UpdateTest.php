<?php

namespace Tests\Feature\AccountAction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AccountAction;
use Str;
use Arr;

class UpdateTest extends TestCase
{
    public function test_controller_and_request()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $creator = $this->makeAuth([], $accountAction->creator);
        $this->actingAs($creator);
        $route = route('account-action.update', ['accountAction' => $accountAction]);

        # Case required is null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'required' => null,
            'requiredRoleKeys' => ['administrator', 'customer', 'tester'],
            '_requiredModelRelationships' => ['requiredRoles'],
        ];

        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
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
                        ->has('requiredRoles.2.key')
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

        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
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
     * Case is manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountAction = AccountAction::inRandomOrder()->first();
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountAction = AccountAction::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountAction->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $accountAction = AccountAction::inRandomOrder()->first();
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(403);
    }

    /**
     * Case is creator of game but lack update_game permission
     */
    public function test_middleware_fail_2()
    {
        $accountAction = AccountAction::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $accountAction->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(403);
    }
}
