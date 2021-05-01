<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class DiscountCodeTradingTest extends TestCase
{
    public function test_freshDatabaseForTest()
    {
        $user = User::inRandomOrder()->first();
        $this->actingAs($user);
        $this->seed(\Database\Seeders\ForTestSeeder::class);

        $this->assertTrue(true);
    }

    public function test_buy()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code-trading.buy', ['discountCode' => $discountCode]);
        $user = User::whereNotIn('id', $discountCode->buyers->pluck('id'))
            ->inRandomOrder()->first();
        $user->update([
            'silver_coin' => $discountCode->price + rand(0, 100000),
        ]);

        $res = $this->actingAs($user)
            ->json('post', $route);

        $res->assertStatus(200);
        $this->assertDatabaseHas('discount_code_has_been_bought_by_users', [
            'user_id' => $user->getKey(),
            'discount_code' => $discountCode->getKey(),
        ]);
    }

    public function test_routeBuyMiddleware()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code-trading.buy', ['discountCode' => $discountCode]);

        /**
         * auth
         * ---------------
         * buyableDiscountCode - enough silver coin
         */
        $user = User::whereNotIn('id', $discountCode->buyers->pluck('id'))
            ->inRandomOrder()->first();
        $this->actingAs($user);
        $user->update([
            'silver_coin' => 0,
        ]);
        $discountCode->update(Arr::random([
            [
                'offered_at' => Carbon::now()->addMinute(),
                'offer_closed_at' => Carbon::now()->subMinute(),
            ],
            [
                'offered_at' => Carbon::now()->addMinute(),
                'offer_closed_at' => null,
            ],
            [
                'offered_at' => null,
                'offer_closed_at' => Carbon::now()->subMinute(),
            ],
        ]));

        # Case 0 - 0
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        # Case 0 - 1
        $user->update([
            'silver_coin' => $discountCode->price + rand(0, 100),
        ]);
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        # Case 1 - 0
        $discountCode->update([
            'offered_at' => Arr::random([Carbon::now()->subMinute(), null]),
            'offer_closed_at' => Arr::random([Carbon::now()->addMinute(), null]),
        ]);
        $user->update([
            'silver_coin' => 0,
        ]);
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        # Case 1 - 1
        $user->update([
            'silver_coin' => $discountCode->price + rand(0, 100),
        ]);
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(200);
    }

    public function test_carefully()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->test_routeBuyMiddleware();
        }
    }
}
