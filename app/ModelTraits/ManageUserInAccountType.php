<?php

namespace App\ModelTraits;

use App\Models\User;

trait ManageUserInAccountType
{
    /**
     * Determine whether user
     * Can use this account type to create game
     *
     * @return boolean
     */
    public function isUsableUser(User $user): bool
    {
        return !is_null($user->usableAccountTypes()->where('account_type_id', $this->getKey())->first());
    }

    /**
     * Determine whether user
     * Can approve accounts created by this account type
     *
     * @return boolean
     */
    public function isApprovableUser(User $user): bool
    {
        return !is_null($user->approvableAccountTypes()->where('account_type_id', $this->getKey())->first());
    }
}
