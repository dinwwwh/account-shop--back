<?php

namespace App\ModelTraits;

use Carbon\Carbon;

trait HelperForDiscountCode
{
    /**
     * Mutate input to must a DiscountCode
     *
     * @param string or App\Models\AccountType as $accountType
     * @return App\Models\DiscountCode or null
     */
    public static function mustBeDiscountCode($discountCode)
    {
        if (!($discountCode instanceof static)) {
            if (is_string($discountCode) || is_numeric($discountCode)) {
                $discountCode = static::find($discountCode);
            } else {
                return null;
            }
        }

        return $discountCode;
    }

    /**
     * Calculate Discount
     *
     * @param int $fee use to calculate discount
     * @param int $cost use to restrict apply discount
     * @return int
     */
    public function calculateDiscount(int $fee, int $cost = null)
    {
        $cost = is_null($cost) ? $fee : $cost;

        $discount = 0;

        if (
            (is_null($this->maximum_price) || $cost <= $this->maximum_price)
            && (is_null($this->minimum_price) || $cost >= $this->minimum_price)
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

    /**
     * Check whether this discount code usable
     *
     * @return boolean
     */
    public function check()
    {
        return (is_null($this->usable_at) || $this->usable_at->lte(Carbon::now()))
            && (is_null($this->usable_closed_at) || $this->usable_closed_at->gte(Carbon::now()));
    }
}
