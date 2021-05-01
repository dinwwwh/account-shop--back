<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountCodeTradingController extends Controller
{
    /**
     * User buy a account.
     *
     * @param  \App\Models\DiscountCode  $discountCode
     * @return \Illuminate\Http\Response
     */
    public function buy(DiscountCode $discountCode)
    {
        $result = false;
        try {
            DB::beginTransaction();

            if (
                auth()->user()->reduceSilverCoin($discountCode->price)
            ) {
                $result = true;
                $discountCode->buyers()->attach(auth()->user()->getKey());
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi không xác định.',
            ], 500);
        }

        if ($result) {
            return new DiscountCodeResource($discountCode);
        } else {
            return response()->json([
                'message' => 'Mua phiếu giảm giá thất bại.',
            ], 500);
        }
    }
}
