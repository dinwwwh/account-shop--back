<?php

namespace App\ModelTraits;

trait ManageAccountFeeInAccount
{
    public function calculateFee()
    {
        $accountFees = $this->accountType->accountFees;
        $cost = $this->cost;
        $fee = 0;

        foreach ($accountFees ?? [] as $accountFee) {
            if (
                (is_null($accountFee->maximum_cost) && $cost <= $accountFee->maximum_cost)
                && (is_null($accountFee->minimum_cost) && $cost >= $accountFee->minimum_cost)
            ) {
                $temporaryFee = $cost * $accountFee->percentage_cost / 100;
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
