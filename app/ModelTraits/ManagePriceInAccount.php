<?php

namespace App\ModelTraits;

trait ManagePriceInAccount
{
    /**
     * To calculate temporary price to consult to buy
     * Not apply any discount code,
     * This is max price
     *
     * @return int
     */
    public function calculateTemporaryPrice(): int
    {
        return $this->cost + $this->calculateFee();
    }

    /**
     * To calculate price to buy,
     * It's price user can buy it.
     *
     * @param string $discountCode
     * @return int or array
     */
    public function calculatePrice($discountCode = null, $detail = false)
    {
        $discountCode = null;

        $discount = is_null($discountCode)
            ? 0
            : $discountCode->calculateDiscount($this->calculateFee(), $this->cost);
        $fee = $this->calculateFee() <= $discount
            ? 0
            : $this->calculateFee() - $discount;


        if ($detail) {
            return [
                'cost' => $this->cost,
                'fee' => $fee,
            ];
        }

        return (int)($this->cost + $fee);
    }
}
