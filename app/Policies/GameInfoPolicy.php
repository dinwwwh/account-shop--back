<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\GameInfo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GameInfoPolicy
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
     * @param  \App\Models\GameInfo  $gameInfo
     * @return mixed
     */
    public function view(User $user, GameInfo $gameInfo)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function create(User $user, Game $game)
    {
        return $user->can('update', $game);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GameInfo  $gameInfo
     * @return mixed
     */
    public function update(User $user, GameInfo $gameInfo)
    {
        return $user->can('update', $gameInfo->game);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GameInfo  $gameInfo
     * @return mixed
     */
    public function delete(User $user, GameInfo $gameInfo)
    {
        return $user->can('update', $gameInfo->game);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GameInfo  $gameInfo
     * @return mixed
     */
    public function restore(User $user, GameInfo $gameInfo)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GameInfo  $gameInfo
     * @return mixed
     */
    public function forceDelete(User $user, GameInfo $gameInfo)
    {
        //
    }
}
