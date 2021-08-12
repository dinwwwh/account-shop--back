<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
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
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function view(User $user, Coupon $coupon)
    {
        //
    }

    /**
     * Determine whether the user can read sensitive infos.
     *
     */
    public function readSensitiveInfos(User $user, Coupon $coupon): bool
    {
        // User is manager
        if ($user->hasPermissionTo('manage_coupon')) return true;

        // User is buyer
        if (!is_null(
            $coupon->buyers()
                ->where($user->getKeyName(), $user->getKey())
                ->first()
        )) return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     */
    public function create(User $user): bool
    {
        if ($user->hasPermissionTo('create_coupon')) return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     */
    public function update(User $user, Coupon $coupon): bool
    {
        if (!$user->hasPermissionTo('update_coupon')) return false;

        if ($coupon->creator_id === $user->getKey()) return true;

        if ($user->hasPermissionTo('manage_coupon')) return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     */
    public function delete(User $user, Coupon $coupon): bool
    {
        if (!$user->hasPermissionTo('delete_coupon')) return false;

        if ($coupon->creator_id === $user->getKey()) return true;

        if ($user->hasPermissionTo('manage_coupon')) return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     */
    public function restore(User $user, Coupon $coupon): bool
    {
        return $this->delete($user, $coupon);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     */
    public function forceDelete(User $user, Coupon $coupon): bool
    {
        if (!$user->hasPermissionTo('force_delete_coupon')) return false;

        if ($coupon->creator_id === $user->getKey()) return true;

        if ($user->hasPermissionTo('manage_coupon')) return true;

        return false;
    }
}
