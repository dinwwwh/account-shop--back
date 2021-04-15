<?php

namespace App\Policies;

use App\Models\AccountFee;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountFeePolicy
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
     * @param  \App\Models\AccountFee  $accountFee
     * @return mixed
     */
    public function view(User $user, AccountFee $accountFee)
    {
        //
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
        return $user->can('update', $accountType);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountFee  $accountFee
     * @return mixed
     */
    public function update(User $user, AccountFee $accountFee)
    {
        return $user->can('update', $accountFee->accountType);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountFee  $accountFee
     * @return mixed
     */
    public function delete(User $user, AccountFee $accountFee)
    {
        return $user->can('update', $accountFee->accountType);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountFee  $accountFee
     * @return mixed
     */
    public function restore(User $user, AccountFee $accountFee)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountFee  $accountFee
     * @return mixed
     */
    public function forceDelete(User $user, AccountFee $accountFee)
    {
        //
    }
}
