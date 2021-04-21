<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;

class ManageAccountFeeInAccountTest extends TestCase
{
    public function calculateFee()
    {
        $account = Account::inRandomOrder()->first();
    }

    public function testCarefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->calculateFee();
        }
    }
}
