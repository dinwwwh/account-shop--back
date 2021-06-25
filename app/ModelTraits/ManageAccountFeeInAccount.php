<?php

namespace App\ModelTraits;

trait ManageAccountFeeInAccount
{
    public function calculateFee(): int
    {
        return  $this->accountType->calculateFee($this->cost);
    }
}
