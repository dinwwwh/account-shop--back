<?php

namespace App\ModelTraits;

use App\Helpers\DiscountCodeHelper;
use App\Models\DiscountCode;

trait ManagePriceInAccount
{
    /**
     * To calculate temporary price to consult to buy
     * Not apply any discount code,
     * This is max price
     *
     * @return integer
     */
    public function calculateTemporaryPrice()
    {
        return $this->cost + $this->calculateFee();
    }

    /**
     * To calculate price to buy,
     * It's price user can buy it.
     *
     * @param string $discountCode
     * @return integer or array
     */
    public function calculatePrice($discountCode, $detail = false)
    {
        $discountCode = DiscountCodeHelper::mustBeDiscountCode($discountCode);
        if (
            is_null($discountCode)
            || !$discountCode->supportedGames->contains($this->accountType->game)
        ) {
            $discountCode = null;
        }

        $discount = is_null($discountCode)
            ? 0
            : $discountCode->calculateDiscount($this->cost);
        $fee = $this->calculateFee() < $discount
            ? 0
            : $this->calculateFee() - $discount;


        if ($detail) {
            return [
                'cost' => $this->cost,
                'fee' => $fee,
            ];
        }

        return $this->cost + $fee;
    }
}
