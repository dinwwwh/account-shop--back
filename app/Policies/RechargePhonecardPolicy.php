<?php

namespace App\Policies;

use App\Models\RechargePhonecard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RechargePhonecardPolicy
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
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function view(User $user, RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can start approving this model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function startApproving(User $user, RechargePhonecard $rechargePhonecard)
    {
        return $user->hasPermissionTo('approve_recharge_phonecard')
            && $rechargePhonecard->status === config('recharge-phonecard.statuses.pending');
    }

    /**
     * Determine whether the user can end approving this model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function endApproving(User $user, RechargePhonecard $rechargePhonecard)
    {
        if (
            $user->hasPermissionTo('approve_recharge_phonecard')
            && $rechargePhonecard->status === config('recharge-phonecard.statuses.approving')
            && $rechargePhonecard->approver_id === $user->getKey()
        ) {
            return true;
        }

        return $user->hasPermissionTo('approve_recharge_phonecard')
            && $user->hasPermissionTo('manage_recharge_phonecard')
            && $rechargePhonecard->status === config('recharge-phonecard.statuses.approving');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function update(User $user, RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function delete(User $user, RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function restore(User $user, RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return mixed
     */
    public function forceDelete(User $user, RechargePhonecard $rechargePhonecard)
    {
        //
    }
}
