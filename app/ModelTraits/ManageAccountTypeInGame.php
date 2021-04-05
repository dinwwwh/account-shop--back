<?php

namespace App\ModelTraits;

use Illuminate\Database\Eloquent\Collection;
use App\Models\AccountType;
use App\Helpers\AccountTypeHelper;

trait ManageAccountTypeInGame
{
    /**
     * Return account types that current user can use it
     * to create account
     *
     * @return App\Models\AccountType
     */
    public function getAccountTypesThatCurrentUserCanUse()
    {
        $result = new Collection;

        if (!auth()->check()) {
            return $result;
        }

        foreach (auth()->user()->roles as $role) {
            $accountTypes = $role->belongsToMany(AccountType::class, 'role_can_used_account_type')
                ->where('game_id', $this->getKey())
                ->get();
            foreach ($accountTypes as $accountType) {
                if (!$result->contains($accountType)) {
                    $result->push($accountType);
                }
            }

            # If It don't have any AccountType then treat all AccountType role can
            if ($result->isEmpty()) {
                return AccountType::where('game_id', $this->id)->get();
            }
        }
        return $result;
    }

    /**
     * Determine whether current user can use $accountType
     * to create account
     *
     * @return boolean
     */
    public function checkCurrentUserCanUseAccountType($accountType)
    {
        $accountType = AccountTypeHelper::mustBeAccountType($accountType);
        $accountTypesThatCurrentUserCanUse = $this->getAccountTypesThatCurrentUserCanUse();

        return $accountTypesThatCurrentUserCanUse->contains($accountType);
    }

    // /**
    //  * Give current user use $accountType
    //  *
    //  * @return void
    //  */
    // public function giveUserUseAccountType($user ,$accountType)
    // {
    //     $accountType = AccountTypeHelper::mustBeAccountType($accountType);

    //     return $accountType->rolesCanUsedAccountType()->attach($user)
    // }
}
