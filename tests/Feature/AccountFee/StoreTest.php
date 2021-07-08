<?php

namespace Tests\Feature\AccountFee;

use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function test_controller_and_request()
    {
        $accountType = AccountType::where('creator_id', '!=', null)->inRandomOrder()->first();
        $route = route('account-fee.store', ['accountType' => $accountType]);
        $creator = $this->makeAuth([], $accountType->game->creator);

        $data = [
            'maximumCost' => rand(1, 10000),
            'minimumCost' => rand(1, 10000),
            'maximumFee' => rand(1, 10000),
            'minimumFee' => rand(1, 10000),
            'percentageCost' => rand(1, 100),
        ];

        $res = $this->actingAs($creator)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->where('data.maximumCost', $data['maximumCost'])
                ->where('data.minimumCost', $data['minimumCost'])
                ->where('data.maximumFee', $data['maximumFee'])
                ->where('data.minimumFee', $data['minimumFee'])
                ->where('data.percentageCost', $data['percentageCost'])
        );
    }

    /**
     * Case is manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-fee.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(201);
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
        $route = route('account-fee.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(201);
    }

    /**
     * Case is manager but lack update_game permission
     */
    public function test_middleware_fail_1()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-fee.store', ['accountType' => $accountType]);
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
        $route = route('account-fee.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }
}
