<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Game;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function view(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can view sensitive info of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function viewSensitiveInfo(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
            // && in_array($account->status_code, [])
        ) {
            return true;
        }

        # user is buyer
        if (
            $user->is($account->buyer)
            && in_array($account->status_code, [840, 880])
        ) {
            return true;
        }

        # user can approve account
        if (
            $this->approve($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            $user->is($account->creator)
            && in_array($account->status_code, [440, 0])
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can manage the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo('manage_account');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function create(User $user, AccountType $accountType)
    {
        return $user->hasPermissionTo('create_account')
            && $accountType->checkUserCanUse($user);
    }

    /**
     * Determine whether the user can approve models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function approve(User $user, Account $account)
    {
        return $user->hasPermissionTo('approve_account')
            && $account->status_code >= 0
            && $account->status_code <= 99;
    }

    /**
     * Determine whether the user can buy models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function buy(User $user, Account $account)
    {
        return  $account->status_code >= 400
            && $account->status_code <= 499
            && is_null($account->buyer_id);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function update(User $user, Account $account)
    {
        if (!$user->hasPermissionTo('update_account')) {
            return false;
        }

        # Case: $user is creator
        if (
            $user->is($account->creator)
            && in_array($account->status_code, [0, 440])
        ) {
            return true;
        }

        # Case: $user is manager
        if (
            $this->manage($user)
            && in_array($account->status_code, [0, 440, 480])
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function delete(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function restore(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function forceDelete(User $user, Account $account)
    {
        //
    }
}
