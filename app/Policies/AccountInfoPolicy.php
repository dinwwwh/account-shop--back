<?php

namespace App\Policies;

use App\Models\AccountInfo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\AccountType;

class AccountInfoPolicy
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
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return mixed
     */
    public function view(User $user, AccountInfo $accountInfo)
    {
        //
    }

    /**
     * Determine whether the user is manager of all account infos
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->can('manage', 'App\Models\AccountType');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user, AccountType $accountType)
    {
        return $user->can('update', $accountType)
            || $this->manage($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return mixed
     */
    public function update(User $user, AccountInfo $accountInfo)
    {
        return $user->can('update', $accountInfo->accountType)
            || $this->manage($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return mixed
     */
    public function delete(User $user, AccountInfo $accountInfo)
    {
        return $user->can('update', $accountInfo->accountType)
            || $this->manage($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return mixed
     */
    public function restore(User $user, AccountInfo $accountInfo)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return mixed
     */
    public function forceDelete(User $user, AccountInfo $accountInfo)
    {
        //
    }
}
