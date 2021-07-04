<?php

namespace App\Observers;

use App\Models\Game;

class GameObserver
{
    /**
     * Handle the Game "created" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function created(Game $game)
    {
        //
    }

    /**
     * Handle the Game "updated" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function updated(Game $game)
    {
        //
    }

    /**
     * Handle the Game "deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function deleted(Game $game)
    {
        // Soft delete game's image
        optional($game->representativeImage)->delete();
    }

    /**
     * Handle the Game "restored" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function restored(Game $game)
    {
        //
    }

    /**
     * Handle the Game "force deleted" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        // Hard delete game's image
        optional($game->representativeImage)->forceDelete();
    }
}
