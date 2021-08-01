<?php

namespace App\Observers;

use App\Models\RechargePhonecard;

class RechargePhonecardObserver
{
    /**
     * Handle the RechargePhonecard "created" event.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return void
     */
    public function created(RechargePhonecard $rechargePhonecard)
    {
        $this->payToCreator($rechargePhonecard);
    }

    /**
     * Handle the RechargePhonecard "updated" event.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return void
     */
    public function updated(RechargePhonecard $rechargePhonecard)
    {
        $this->payToCreator($rechargePhonecard);
    }

    /**
     * Handle the RechargePhonecard "deleted" event.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return void
     */
    public function deleted(RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Handle the RechargePhonecard "restored" event.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return void
     */
    public function restored(RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Handle the RechargePhonecard "force deleted" event.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return void
     */
    public function forceDeleted(RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Pay success recharge phonecard to creator
     *
     * @return void
     */
    public function payToCreator(RechargePhonecard $rechargePhonecard)
    {
        if (
            $rechargePhonecard->status === config('recharge-phonecard.statuses.success')
            && $rechargePhonecard->received_value > 0
            && is_null($rechargePhonecard->paid_at)
        ) {
            $rechargePhonecard->creator->update([
                'gold_coin' => $rechargePhonecard->creator->gold_coin + $rechargePhonecard->received_value,
            ]);

            $rechargePhonecard->update([
                'paid_at' => now(),
            ]);
        }
    }
}
