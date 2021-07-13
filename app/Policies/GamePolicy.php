<?php

namespace App\Policies;

use App\Models\DiscountCode;
use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
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
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function view(User $user, Game $game)
    {
        return true;
    }

    /**
     * Determine whether the user is manager of all games
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo('manage_game');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create_game')
            || $this->manage($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function update(User $user, Game $game)
    {
        if ($this->manage($user)) {
            return true;
        }

        return $user->hasPermissionTo('update_game')
            && $user->is($game->creator);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function delete(User $user, Game $game)
    {
        if ($this->manage($user)) {
            return true;
        }

        return $user->hasPermissionTo('delete_game')
            && $user->is($game->creator);
    }

    /**
     * Determine whether the user can allow a discount code
     * for $game to discount price.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @param  \App\Models\DiscountCode  $discountCode
     * @return mixed
     */
    public function allowDiscountCode(User $user, Game $game, DiscountCode $discountCode)
    {
        return $this->update($user, $game);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function restore(User $user, Game $game)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function forceDelete(User $user, Game $game)
    {
        //
    }
}
