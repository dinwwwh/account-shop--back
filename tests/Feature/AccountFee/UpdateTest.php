<?php

namespace Tests\Feature\AccountFee;

use App\Models\AccountFee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function test_controller()
    {
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.update', ['accountFee' => $accountFee]);
        $this->actingAs($this->makeAuth([]));

        $data = [
            'maximumCost' => rand(1, 10000),
            'minimumCost' => rand(1, 10000),
            'maximumFee' => rand(1, 10000),
            'minimumFee' => rand(1, 10000),
            'percentageCost' => rand(1, 100),
        ];
        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
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
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.update', ['accountFee' => $accountFee]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountFee = AccountFee::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountFee->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-fee.update', ['accountFee' => $accountFee]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack update_game manage_game permission
     */
    public function test_middleware_fail()
    {
        $accountFee = AccountFee::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game', 'manage_game'], $accountFee->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-fee.update', ['accountFee' => $accountFee]);
        $this->json('put', $route)->assertStatus(403);
    }
}
