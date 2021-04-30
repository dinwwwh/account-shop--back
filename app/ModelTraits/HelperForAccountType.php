<?php

namespace App\ModelTraits;

trait HelperForAccountType
{
    /**
     * Mutate input to must a AccountType
     *
     * @return App\Models\AccountType or null
     */
    public static function mustBeAccountType($accountType)
    {
        if (!($accountType instanceof static)) {
            if (is_string($accountType) || is_numeric($accountType)) {
                $accountType = static::find($accountType);
            } else {
                return null;
            }
        }

        return $accountType;
    }
}
