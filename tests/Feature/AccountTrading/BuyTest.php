<?php

namespace Tests\Feature\AccountTrading;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuyTest extends TestCase
{
    public function test_controller_and_request()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 400)
            ->where('status_code', '<=', 499)
            ->first();


        $route = route('account-trading.buy', ['account' => $account]);
        $user = User::where('id', '!=', $account->creator->getKey())->inRandomOrder()->first();
        $goldCoin = rand($account->calculateTemporaryPrice(), $account->calculateTemporaryPrice() + 200000);
        $user->gold_coin = $goldCoin;
        $user->save();

        # Case: enough gold coin to buy account
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'gold_coin' => ($goldCoin - $account->calculateTemporaryPrice()),
        ]);

        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 400)
            ->where('status_code', '<=', 499)
            ->first();
        $user = User::where('id', '!=', $account->creator->id)->inRandomOrder()->first();
        $route = route('account-trading.buy', ['account' => $account]);
        # Case: don't enough gold coin to buy account
        $user->gold_coin = rand(1, $account->cost - 1);
        $user->save();
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_with_valid_account()
    {
        $validAccount = Account::where('status_code', '>=', 400)
            ->where('status_code', '<=', 499)
            ->first();
        $this->actingAs($this->makeAuth([]));

        # valid account
        $res = $this->json('post', route('account-trading.buy', ['account' => $validAccount]));
        $this->assertTrue($res->status() == 422 || $res->status() == 200);
    }

    public function test_middleware_with_invalid_account()
    {
        $invalidAccount = Account::where('status_code', '<', 400)
            ->orWhere('status_code', '>', 499)
            ->first();
        $this->actingAs($this->makeAuth([]));

        $this->json('post', route('account-trading.buy', ['account' => $invalidAccount]))
            ->assertStatus(403);
    }
}
