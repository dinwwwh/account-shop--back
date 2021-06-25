<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\user;
use App\Models\AccountType;

class ManageAccountFeeInAccountTypeTest extends TestCase
{
    public function test_calculateFee()
    {
        $accountType = AccountType::inRandomOrder()->first();

        $costNumber = rand(0, 999999);
        $fee1 = $accountType->calculateFee($costNumber);
        $fee2 = 0;

        foreach ($accountType->accountFees as $accountFee) {

            if (
                (is_null($accountFee->maximum_cost) || $costNumber <= $accountFee->maximum_cost)
                && (is_null($accountFee->minimum_cost) || $costNumber >= $accountFee->minimum_cost)
            ) {
                $temporaryFee = $costNumber * $accountFee->percentage_cost / 100;
                $temporaryFee += $accountFee->direct_fee;
                $temporaryFee = is_numeric($accountFee->maximum_fee) && $accountFee->maximum_fee < $temporaryFee
                    ? $accountFee->maximum_fee
                    : $temporaryFee;
                $temporaryFee = is_numeric($accountFee->minimum_fee) && $accountFee->minimum_fee > $temporaryFee
                    ? $accountFee->minimum_fee
                    : $temporaryFee;
                $fee2 += $temporaryFee;
            }
        }

        $this->assertEquals($fee1, (int)$fee2);
    }

    public function test_carefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->test_calculateFee();
        }
    }
}
