<?php

namespace Tests\Feature\AccountInfo;

use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;

class StoreTest extends TestCase
{
    public function test_controller_and_request()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->actingAs($this->makeAuth([]));

        # Case normal rule
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
            '_requiredModelRelationships' => ['rule']
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
                        ->etc()
                )
        );

        # Case advanced rule
        $data['rule'] = [
            'required' => null,
            'requiredRoleKeys' => ['tester'],
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
                        ->where('rule.required', null)
                        ->where('rule.requiredRoles.0.key', 'tester')
                        ->etc()
                )
        );
    }

    /**
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('put', $route)->assertStatus(403);
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
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('put', $route)->assertStatus(403);
    }
}
