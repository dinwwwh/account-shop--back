<?php

namespace App\Policies;

use App\Models\AccountType;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Game;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountTypePolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function view(User $user, AccountType $accountType)
    {
        return true;
    }

    /**
     * Determine whether the user is manager of all account types
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->can('manage', 'App\Models\Game');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user, Game $game)
    {
        return $user->can('update', $game)
            || $this->manage($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function update(User $user, AccountType $accountType)
    {
        return $user->can('update', $accountType->game)
            || $this->manage($user);
    }

    /**
     * Determine whether the user can attach coupon to this account type.
     * To use it discount account of this account type.
     *
     */
    public function attachCoupon(User $user, AccountType $accountType, Coupon $coupon): bool
    {
        return $this->update($user, $accountType);
    }

    /**
     * Determine whether the user can detach coupon to this account type.
     * To disable coupon for account of this account type.
     *
     */
    public function detachCoupon(User $user, AccountType $accountType, Coupon $coupon): bool
    {
        return $this->update($user, $accountType);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function delete(User $user, AccountType $accountType)
    {
        return  false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function restore(User $user, AccountType $accountType)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function forceDelete(User $user, AccountType $accountType)
    {
        //
    }
}
