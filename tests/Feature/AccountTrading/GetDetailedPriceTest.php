<?php

namespace Tests\Feature\AccountTrading;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetDetailedPriceTest extends TestCase
{
    public function test_controller()
    {
        $account = Account::inRandomOrder()->first();
        $expectedResult = $account->calculatePrice(null, true);

        $route =  route('account-trading.detailed-price', ['account' => $account]);
        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson([
            'data' => $expectedResult,
        ]);
    }
}
