<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    /**
     * Handle the Account "created" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function created(Account $account)
    {
        //
    }

    /**
     * Handle the Account "updated" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function updated(Account $account)
    {
        //
    }

    /**
     * Handle the Account "deleted" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function deleted(Account $account)
    {

        // Soft delete all account's images
        $account->images->each(fn ($file) => $file->delete());
    }

    /**
     * Handle the Account "restored" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function restored(Account $account)
    {
        //
    }

    /**
     * Handle the Account "force deleted" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function forceDeleted(Account $account)
    {
        // Hard delete all account's images
        $account->images->each(fn ($file) => $file->forceDelete());
    }
}
