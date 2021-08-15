<?php

namespace App\ModelTraits;

use App\Models\Coupon;
use App\Models\User;

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
    public function calculatePrice(?User $user, $couponCode = null, $detail = false)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        $discount = !is_null($coupon) && $coupon->isUsable($user)
            ? $coupon->calculateDiscount($this->calculateFee(), $this->cost)
            : 0;
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

    public function calculatePriceAndUseCouponNow(User $user, $couponCode = null, $detail = false)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        $discount = is_null($coupon)
            ? 0
            : $coupon->calculateDiscountAndUseNow($user, $this->calculateFee(), $this->cost);
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
