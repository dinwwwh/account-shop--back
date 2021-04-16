<?php

namespace App\Helpers;

use App\Models\DiscountCode;

class DiscountCodeHelper
{
    /**
     * Mutate input to must a DiscountCode
     *
     * @param string or App\Models\AccountType as $accountType
     * @return App\Models\DiscountCode or null
     */
    public static function mustBeDiscountCode($discountCode)
    {
        if (!($discountCode instanceof DiscountCode)) {
            if (is_string($discountCode) || is_numeric($discountCode)) {
                $discountCode = DiscountCode::find($discountCode);
            } else {
                return null;
            }
        }

        return $discountCode;
    }
}
