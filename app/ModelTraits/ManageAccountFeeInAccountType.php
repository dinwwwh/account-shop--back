<?php

namespace App\ModelTraits;

trait ManageAccountFeeInAccountType
{
    public function calculateFee($cost): int
    {
        $accountFees = $this->accountFees;
        $fee = 0;

        foreach ($accountFees ?? [] as $accountFee) {
            if (
                (is_null($accountFee->maximum_cost) || $cost <= $accountFee->maximum_cost)
                && (is_null($accountFee->minimum_cost) || $cost >= $accountFee->minimum_cost)
            ) {
                $temporaryFee = $cost * $accountFee->percentage_cost / 100;
                $temporaryFee += $accountFee->direct_fee;
                $temporaryFee = is_numeric($accountFee->maximum_fee) && $temporaryFee > $accountFee->maximum_fee
                    ? $accountFee->maximum_fee
                    : $temporaryFee;
                $temporaryFee = is_numeric($accountFee->minimum_fee) && $temporaryFee < $accountFee->minimum_fee
                    ? $accountFee->minimum_fee
                    : $temporaryFee;
                $fee += $temporaryFee;
            }
        }

        return $fee;
    }
}
