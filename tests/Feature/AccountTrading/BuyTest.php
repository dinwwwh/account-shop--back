<?php

namespace Tests\Feature\AccountTrading;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuyTest extends TestCase
{
    public function test_controller_and_middleware_success()
    {
        $config = config('account.buyable_status_codes', []);
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $oldStatusCode = $account->latestAccountStatus->code;
        $route = route('account-trading.buy', ['account' => $account]);
        $user = $this->makeAuth([], [], true);
        $price = $account->calculateTemporaryPrice();
        $goldCoin = rand($price, $price + 200000);
        $user->gold_coin = $goldCoin;
        $user->save();

        # Case: enough gold coin to buy account
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(204);
        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'gold_coin' => ($goldCoin - $account->calculateTemporaryPrice()),
        ]);

        $this->assertDatabaseHas('accounts', [
            'buyer_id' => $user->getKey(),
            'sold_at_price' => $price,
        ]);

        $this->assertDatabaseHas('account_statuses', [
            'creator_id' => $user->getKey(),
            'code' =>  $oldStatusCode + 400,
        ]);
    }

    public function test_middleware_fail_invalid_account()
    {
        $config = config('account.buyable_status_codes', []);
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account-trading.buy', ['account' => $account]);
        $user = $this->makeAuth([], [], true);
        $this->actingAs($user);

        $res = $this->json('post', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_not_enough_money()
    {
        $config = config('account.buyable_status_codes', []);
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account-trading.buy', ['account' => $account]);
        $user = $this->makeAuth([], [], true);
        $price = $account->calculateTemporaryPrice();
        $user->gold_coin = $price - 1;
        $user->save();

        # Case: enough gold coin to buy account
        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(422);
    }
}
