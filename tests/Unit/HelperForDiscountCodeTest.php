<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DiscountCode;
use Carbon\Carbon;

class HelperForDiscountCodeTest extends TestCase
{
    public function test_calculateDiscount()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();

        /**
         * third argument is null (cost = null)
         * then treat cost = fee
         */

        # cost < minimum price
        $fee = $discountCode->minimum_price - rand(1, 10000);
        $this->assertEquals(0, $discountCode->calculateDiscount($fee));

        # cost > maximum price
        $fee = $discountCode->maximum_price + rand(1, 10000);
        $this->assertEquals(0, $discountCode->calculateDiscount($fee));

        #cost between maximum and minimum price
        $fee = rand($discountCode->minimum_price, $discountCode->maximum_price);
        $assertDiscount = ($fee * $discountCode->percentage_discount / 100);
        $assertDiscount += $discountCode->direct_discount;
        $assertDiscount = is_null($discountCode->maximum_discount) || $assertDiscount <= $discountCode->maximum_discount
            ? $assertDiscount
            : $discountCode->maximum_discount;
        $assertDiscount = is_null($discountCode->minimum_discount) || $assertDiscount >= $discountCode->minimum_discount
            ? $assertDiscount
            : $discountCode->minimum_discount;
        $this->assertEquals($assertDiscount, $discountCode->calculateDiscount($fee));

        /**
         * third argument is number
         *
         */
        $fee = rand(0, 10000000);

        # cost < minimum price
        $cost = $discountCode->minimum_price - rand(1, 10000);
        $this->assertEquals(0, $discountCode->calculateDiscount($fee, $cost));

        # cost > maximum price
        $fee = $discountCode->maximum_price + rand(1, 10000);
        $this->assertEquals(0, $discountCode->calculateDiscount($fee, $cost));

        #cost between maximum and minimum price
        $cost = rand($discountCode->minimum_price, $discountCode->maximum_price);
        $assertDiscount = ($fee * $discountCode->percentage_discount / 100);
        $assertDiscount += $discountCode->direct_discount;
        $assertDiscount = is_null($discountCode->maximum_discount) || $assertDiscount <= $discountCode->maximum_discount
            ? $assertDiscount
            : $discountCode->maximum_discount;
        $assertDiscount = is_null($discountCode->minimum_discount) || $assertDiscount >= $discountCode->minimum_discount
            ? $assertDiscount
            : $discountCode->minimum_discount;
        $this->assertEquals($assertDiscount, $discountCode->calculateDiscount($fee, $cost));
    }

    public function test_check()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $now = Carbon::now();

        # now less than usable_at
        $discountCode->usable_at =  Carbon::now()->addMinutes();
        $this->assertEquals(false, $discountCode->check());

        # now greater than usable_closed_at
        $discountCode->usable_closed_at = Carbon::now()->subMinutes();
        $this->assertEquals(false, $discountCode->check());

        #now between usable_at & usable_closed_at
        $discountCode->usable_closed_at = Carbon::now()->addMinutes();
        $discountCode->usable_at = Carbon::now()->subMinutes();
        $this->assertEquals(true, $discountCode->check());
    }

    public function test_carefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->test_calculateDiscount();
            $this->test_check();
        }
    }
}
