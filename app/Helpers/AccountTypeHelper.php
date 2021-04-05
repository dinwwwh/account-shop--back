<?php

namespace App\Helpers;

use App\Models\AccountType;

class AccountTypeHelper
{
    /**
     * Mutate input to must a AccountType
     *
     * @return App\Models\AccountType or null
     */
    public static function mustBeAccountType($accountType)
    {
        if (!($accountType instanceof AccountType)) {
            if (is_string($accountType) || is_numeric($accountType)) {
                $accountType = AccountType::find($accountType);
            } else {
                return null;
            }
        }

        return $accountType;
    }
}
