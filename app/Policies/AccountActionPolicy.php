<?php

namespace App\Policies;

use App\Models\AccountAction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\AccountType;

class AccountActionPolicy
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
     * @param  \App\Models\AccountAction  $accountAction
     * @return mixed
     */
    public function view(User $user, AccountAction $accountAction)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
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
     * @param  \App\Models\AccountAction  $accountAction
     * @return mixed
     */
    public function update(User $user, AccountAction $accountAction)
    {
        return $user->can('update', $accountAction->accountType);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountAction  $accountAction
     * @return mixed
     */
    public function delete(User $user, AccountAction $accountAction)
    {
        return $user->can('update', $accountAction->accountType);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountAction  $accountAction
     * @return mixed
     */
    public function restore(User $user, AccountAction $accountAction)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountAction  $accountAction
     * @return mixed
     */
    public function forceDelete(User $user, AccountAction $accountAction)
    {
        //
    }
}
