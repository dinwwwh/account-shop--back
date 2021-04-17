<?php

namespace App\ModelTraits;

trait HelperForDiscountCode
{
    public function calculateDiscount(float $price)
    {
        $discount = 0;

        if (
            (is_null($this->maximum_price) && $price <= $this->maximum_price)
            && (is_null($this->minimum_price) && $price >= $this->minimum_price)
        ) {
            $discount = $price * $this->percentage_discount / 100;
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
