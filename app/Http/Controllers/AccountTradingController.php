<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

            // Do something before send account for user
            switch ($account->status_code) {
                case 480:
                    $account->status_code = 880;
                    break;

                case 440:
                    $account->status_code = 840;
                    break;

                default:
                    # code...
                    break;
            }

            // Handle on user
            auth()->user()->reduceGoldCoin($bestPrice);

            // Handle on account
            {
                $account->buyer_id = auth()->user()->id;
                $account->sold_at_price = $bestPrice;
                $account->sold_at = Carbon::now();
                $account->save();
            }

            // When Success
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Mua tài khoản thành công, vui lòng vào lịch sử giao dịch để xem chi tiết.'
        ], 200);
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
