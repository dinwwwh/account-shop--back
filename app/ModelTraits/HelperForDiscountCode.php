<?php

namespace App\ModelTraits;

trait HelperForDiscountCode
{
    /**
     * Calculate Discount
     *
     * @param int $fee use to calculate discount
     * @param int $cost use to restrict apply discount
     * @return int
     */
    public function calculateDiscount($fee, $cost = null)
    {
        $cost = is_null($cost) ? $fee : $cost;

        $discount = 0;

        if (
            (is_null($this->maximum_price) && $cost <= $this->maximum_price)
            && (is_null($this->minimum_price) && $cost >= $this->minimum_price)
        ) {
            $discount = $fee * $this->percentage_discount / 100;
            $discount += $this->direct_discount;
            $discount = is_numeric($this->maximum_discount) && $discount > $this->maximum_discount
                ? $this->maximum_discount
                : $discount;
            $discount = is_numeric($this->minimum_discount) && $discount < $this->minimum_discount
                ? $this->minimum_discount
                : $discount;
        }

        return $discount;
    }
}
