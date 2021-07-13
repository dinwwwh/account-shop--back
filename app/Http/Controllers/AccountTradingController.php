<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountTradingController extends Controller
{
    /**
     * User buy a account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function buy(Request $request, Account $account)
    {
        // Initial data
        $bestPrice = $account->calculatePrice($request->discountCode);

        // Check whether user can buy this account
        if (!auth()->user()->checkEnoughGoldCoin($bestPrice)) {
            return response()->json([
                'message' => 'Bạn không đủ số lượng đồng vàng để mua tài khoản này.',
            ], 422);
        }

        try {
            DB::beginTransaction();
            $oldStatusCode = $account->latestAccountStatus->code;

            // Do something before send account for user
            switch ($oldStatusCode) {
                case 480:
                    $newStatusCode = 880;
                    break;

                case 440:
                    $newStatusCode = 840;
                    break;

                default:
                    throw 'Lack case handle status code given ' . $oldStatusCode;
            }

            // Handle on user
            auth()->user()->reduceGoldCoin($bestPrice);

            // Handle on account
            $account->update([
                'buyer_id' => auth()->user()->getKey(),
                'sold_at_price' => $bestPrice,
                'sold_at' => now(),
            ]);
            $account->accountStatuses()->create([
                'code' => $newStatusCode,
                'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_SOLD
            ]);

            // When Success
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * calculate detail price include cost and fee of account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function getDetailedPrice(Request $request, Account $account)
    {
        return response(['data' => $account->calculatePrice($request->discountCode, true),]);
    }
}
