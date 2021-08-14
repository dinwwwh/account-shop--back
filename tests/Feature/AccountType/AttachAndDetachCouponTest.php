<?php

namespace Tests\Feature\AccountType;

use App\Models\AccountType;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttachAndDetachCouponTest extends TestCase
{

    public function test_attach()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $coupon = Coupon::whereNotIn('id', $accountType->coupons->pluck('id')->toArray())
            ->inRandomOrder()->first();
        $this->actingAs($this->makeAuth());

        $route = route('account-type.coupon.attach', ['accountType' => $accountType, 'coupon' => $coupon]);
        $res = $this->json('post', $route, ['type' => config('coupon.types.discount_to_fees')]);

        $res->assertStatus(204);

        $this->assertDatabaseHas('couponnables', [
            'coupon_id' => $coupon->getKey(),
            'couponnable_type' => AccountType::class,
            'couponnable_id' => $accountType->getKey(),
        ]);
    }

    public function test_detach()
    {
        $count = 0;

        do {
            if ($count > 100) return;
            $accountType = AccountType::inRandomOrder()->first();
            $coupon = $accountType->coupons()->inRandomOrder()->first();
        } while (is_null($coupon));

        $this->actingAs($this->makeAuth());
        $route = route('account-type.coupon.detach', ['accountType' => $accountType, 'coupon' => $coupon]);
        $res = $this->json('delete', $route);

        $res->assertStatus(204);

        $this->assertDatabaseMissing('couponnables', [
            'coupon_id' => $coupon->getKey(),
            'couponnable_type' => AccountType::class,
            'couponnable_id' => $accountType->getKey(),
        ]);
    }
}
