<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\DiscountCode;

class ManagePriceInAccountTest extends TestCase
{
    public function testCalculateTemporaryPrice()
    {
        $account = Account::inRandomOrder()->first();
        $account->cost = rand(1, 100000000);

        $temporaryPrice = $account->calculateTemporaryPrice();
        $this->assertTrue($temporaryPrice === $account->cost + $account->calculateFee());
    }

    public function testCalculatePrice()
    {
        $account = Account::inRandomOrder()->first();
        $account->cost = rand(1, 100000000);
        $discountCode = DiscountCode::inRandomOrder()->first();

        # discount code not support
        $this->assertTrue(
            $account->calculatePrice($discountCode->getKey())
                === $account->cost + $account->calculateFee()
        );

        # discount supported
        $discountCode->supportedGames()->attach($account->accountType->game);
        $this->assertTrue(
            $account->calculatePrice($discountCode->getKey())
                <= $account->cost + $account->calculateFee()
                && $account->calculatePrice($discountCode->getKey())
                >= $account->cost
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
