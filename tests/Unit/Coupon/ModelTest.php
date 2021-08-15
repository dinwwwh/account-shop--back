<?php

namespace Tests\Unit\Coupon;

use App\Models\Coupon;
use App\Models\User;
use Tests\TestCase;

class ModelTest extends TestCase
{
    public function test_price_is_null_calculateDiscountAndUseNow()
    {
        $coupon = Coupon::factory()->state([
            'price' => null,
            'usable_at' => now()->subMinute(),
            'usable_closed_at' => now()->addMinute(),
        ])->create();
        $oldUsedAmount = $coupon->used_amount;

        $coupon->calculateDiscountAndUseNow(null, 999);

        $this->assertDatabaseHas($coupon->getTable(), [
            $coupon->getKeyName() =>  $coupon->getKey(),
            'used_amount' => $oldUsedAmount + 1,
        ]);
    }

    public function test_price_is_not_null_calculateDiscountAndUseNow()
    {
        $coupon = Coupon::factory()->state([
            'price' => 1000,
            'usable_at' => now()->subMinute(),
            'usable_closed_at' => now()->addMinute(),
        ])->create();

        $buyer = $coupon->buyers->random();
        $buyer->pivot->update([
            'used_amount' => $buyer->pivot->amount - 1,
        ]);

        $oldUsedAmount = $buyer->pivot->used_amount;

        $coupon->calculateDiscountAndUseNow($buyer, 999);

        $this->assertDatabaseHas($buyer->pivot->getTable(), [
            'coupon_' . $coupon->getKeyName() =>  $coupon->getKey(),
            'user_' . $buyer->getKeyName() =>  $buyer->getKey(),
            'used_amount' => $oldUsedAmount + 1,
        ]);
    }

    public function test_calculateDiscount()
    {
        $this->assertEquals(1000, Coupon::factory()->state([
            'percentage_discount' => 10,
            'direct_discount' => 1000,
            'maximum_discount' => 1000,
            'minimum_discount' => 1000,
        ])->make()->calculateDiscount(1000));

        $this->assertEquals(0, Coupon::factory()->state([
            'maximum_value' => 10,
        ])->make()->calculateDiscount(100, 9999));

        $this->assertEquals(0, Coupon::factory()->state([
            'minimum_value' => 10,
        ])->make()->calculateDiscount(100, 9));
    }

    public function test_isUsable()
    {
        $this->assertTrue(Coupon::factory()->state([
            'price' => null,
            'usable_at' => now()->subMinute(),
            'usable_closed_at' => now()->addMinute(),
        ])->make()->isUsable(null));
    }

    public function test_canUseByEveryone()
    {
        $this->assertTrue(Coupon::factory()->state([
            'price' => null,
        ])->make()->canUseByEveryone());

        $this->assertFalse(Coupon::factory()->state([
            'price' => 10000,
        ])->make()->canUseByEveryone());
    }

    public function test_isBuyer()
    {
        $coupon = Coupon::factory()->state([
            'price' => 20000
        ])->create();

        # case is null
        $this->assertFalse($coupon->isBuyer(null));

        # case expected true
        $this->assertTrue($coupon->isBuyer($coupon->buyers->random()));

        # case expected false
        $this->assertFalse(
            $coupon->isBuyer(
                User::whereNotIn('id', $coupon->buyers->pluck('id')->toArray())
                    ->inRandomOrder()
                    ->first()
            )
        );
    }

    public function test_isBuyerAndHasUsableAmount()
    {
        $coupon = Coupon::factory()->state([
            'price' => 20000
        ])->create();

        # case is null
        $this->assertFalse($coupon->isBuyerAndHasUsableAmount(null));

        # case expected true has usable amount
        $buyer = $coupon->buyers->random();
        $buyer->pivot->update([
            'used_amount' => $buyer->pivot->amount - 1
        ]);
        $this->assertTrue($coupon->isBuyerAndHasUsableAmount($buyer));

        $buyer->pivot->update([
            'used_amount' => $buyer->pivot->amount
        ]);
        # case expected false not has usable amount
        $this->assertFalse($coupon->isBuyerAndHasUsableAmount($buyer));

        # case expected false
        $this->assertFalse(
            $coupon->isBuyerAndHasUsableAmount(
                User::whereNotIn('id', $coupon->buyers->pluck('id')->toArray())
                    ->inRandomOrder()
                    ->first()
            )
        );
    }

    public function test_isUnexpired()
    {
        $this->assertTrue(Coupon::factory()->state([
            'usable_at' => now()->subMinute(),
            'usable_closed_at' => now()->addMinute(),
        ])->make()->isUnexpired());

        $this->assertFalse(Coupon::factory()->state([
            'usable_at' => now()->addMinute(),
            'usable_closed_at' => now()->addMinute(),
        ])->make()->isUnexpired());

        $this->assertFalse(Coupon::factory()->state([
            'usable_at' => now()->subMinute(),
            'usable_closed_at' => now()->subMinute(),
        ])->make()->isUnexpired());
    }

    public function test_getUsableAmount()
    {
        $coupon = Coupon::factory()->state([
            'amount' => 99
        ])
            ->make();

        #Case normal
        $this->assertEquals(
            $coupon->amount - $coupon->used_amount,
            $coupon->getUsableAmount(),
        );

        #Case exception: used_amount greater than amount
        $coupon->used_amount = $coupon->amount + 1;
        $this->assertEquals(
            0,
            $coupon->getUsableAmount(),
        );
    }
}
