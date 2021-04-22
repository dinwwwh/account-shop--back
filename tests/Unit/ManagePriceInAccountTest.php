<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\DiscountCode;
use App\Models\User;

class ManagePriceInAccountTest extends TestCase
{
    public function testCalculateTemporaryPrice()
    {
        $account = Account::inRandomOrder()->first();

        $temporaryPrice = $account->calculateTemporaryPrice();
        $this->assertTrue($temporaryPrice === $account->cost + $account->calculateFee());
    }

    public function testCalculatePrice()
    {
        $account = Account::inRandomOrder()->first();
        $discountCode = DiscountCode::inRandomOrder()->first();

        # discount code not support
        $this->assertTrue(
            $account->calculatePrice($discountCode->getKey())
                === $account->cost + $account->calculateFee()
        );

        # discount supported
        $discountCode->supportedGames()->attach($account->accountType->game);
        $fee = $account->calculateFee();

        $fee = $fee <= $discountCode->calculateDiscount($fee, $account->cost)
            ? 0
            : $fee - $discountCode->calculateDiscount($fee, $account->cost);

        $this->assertTrue(
            $account->calculatePrice($discountCode->getKey())
                === $fee + $account->cost
        );
    }

    public function testCarefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->testCalculateTemporaryPrice();
            $this->testCalculatePrice();
        }
    }
}
