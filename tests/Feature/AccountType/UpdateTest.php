<?php

namespace Tests\Feature\AccountType;

use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;

class UpdateTest extends TestCase
{
    public function test_controller_and_request()
    {
        // Initial data
        $accountType = AccountType::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([]));

        $data = [
            'name' => Str::random(20),
            'description' => Str::random(20),
            'rolesCanUsedAccountType' => [
                [
                    'key' => 'administrator',
                    'statusCode' => 200
                ]
            ],
            '_requiredModelRelationships' => ['rolesCanUsedAccountType']
        ];
        $res = $this->json('put', route('account-type.update', ['accountType' => $accountType]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->has(
                            'rolesCanUsedAccountType',
                            fn ($json) => $json
                                ->has(
                                    0,
                                    fn ($json) => $json
                                        ->where('key', $data['rolesCanUsedAccountType'][0]['key'])
                                        ->where('pivot.statusCode', $data['rolesCanUsedAccountType'][0]['statusCode'])
                                        ->etc()
                                )
                        )
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
        $route = route('account-type.update', ['accountType' => $accountType]);
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
        $route = route('account-type.update', ['accountType' => $accountType]);
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
        $route = route('account-type.update', ['accountType' => $accountType]);
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
        $route = route('account-type.update', ['accountType' => $accountType]);
        $this->json('put', $route)->assertStatus(403);
    }
}
