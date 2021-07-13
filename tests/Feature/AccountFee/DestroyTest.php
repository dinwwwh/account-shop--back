<?php

namespace Tests\Feature\AccountFee;

use App\Models\AccountFee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    public function test_controller()
    {
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.destroy', ['accountFee' => $accountFee]);
        $user = $this->makeAuth([]);
        $this->actingAs($user);


        $res = $this->json('delete', $route);
        $res->assertStatus(200);

        $this->assertDatabaseMissing('account_fees', [
            'id' => $accountFee->getKey()
        ]);
    }

    /**
     * Case is manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.destroy', ['accountFee' => $accountFee]);
        $this->json('delete', $route)->assertStatus(200);
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
        $route = route('account-fee.destroy', ['accountFee' => $accountFee]);
        $this->json('delete', $route)->assertStatus(200);
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
        $route = route('account-fee.destroy', ['accountFee' => $accountFee]);
        $this->json('delete', $route)->assertStatus(403);
    }
}
