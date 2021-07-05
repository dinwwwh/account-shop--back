<?php

namespace App\Policies;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class DiscountCodePolicy
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
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function view(User $user, DiscountCode $discountCode)
    {
        //
    }

    /**
     * Determine whether the user can manage models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo('manage_discount_code');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create_discount_code');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function buy(User $user, DiscountCode $discountCode)
    {
        $buyableDiscountCode =
            (is_null($discountCode->offered_at) || $discountCode->offered_at->lte(Carbon::now()))
            && (is_null($discountCode->offer_closed_at) || $discountCode->offer_closed_at->gte(Carbon::now()));
        $enoughSilverCoin = $user->checkEnoughSilverCoin($discountCode->price);

        return $buyableDiscountCode && $enoughSilverCoin;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function update(User $user, DiscountCode $discountCode)
    {
        return $user->hasPermissionTo('update_discount_code')
            && ($user->is($discountCode->creator) || $this->manage($user));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function delete(User $user, DiscountCode $discountCode)
    {
        return $user->hasPermissionTo('delete_discount_code')
            && ($user->is($discountCode->creator) || $this->manage($user));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function restore(User $user, DiscountCode $discountCode)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function forceDelete(User $user, DiscountCode $discountCode)
    {
        //
    }
}
