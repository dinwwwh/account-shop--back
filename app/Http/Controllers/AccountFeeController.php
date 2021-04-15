<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountFeeRequest;
use App\Http\Requests\UpdateAccountFeeRequest;
use App\Http\Resources\AccountFeeResource;
use App\Models\AccountFee;
use App\Models\AccountType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AccountFeeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreAccountFeeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountFeeRequest $request, AccountType $accountType)
    {
        // Initialize data
        $accountFeeData = [];
        foreach ([
            'maximumCost',
            'minimumCost',
            'maximumFee',
            'minimumFee',
        ] as $key) {
            if ($request->filled($key)) {
                $snackKey = Str::snake($key);
                $accountFeeData[$snackKey] = $request->$key;
            }
        }

        $accountFeeData['percentage_cost'] = $request->percentageCost;
        $accountFeeData['account_type_id'] = $accountType->getKey();

        try {
            DB::beginTransaction();

            $accountFee = AccountFee::create($accountFeeData);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json([
                'message' => 'Thêm mới lệ phí tài khoản thất bại, vui lòng thử lại sau.'
            ], 500);
        }

        return new AccountFeeResource($accountFee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UpdateAccountFeeRequest  $request
     * @param  \App\Models\AccountFee  $accountFee
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountFeeRequest $request, AccountFee $accountFee)
    {
        // Initialize data
        $accountFeeData = [];
        foreach ([
            'maximumCost',
            'minimumCost',
            'maximumFee',
            'minimumFee',
            'percentageCost'
        ] as $key) {
            if ($request->filled($key)) {
                $snackKey = Str::snake($key);
                $accountFeeData[$snackKey] = $request->$key;
            }
        }

        try {
            DB::beginTransaction();

            $accountFee->update($accountFeeData);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json([
                'message' => 'Thêm mới lệ phí tài khoản thất bại, vui lòng thử lại sau.'
            ], 500);
        }

        return new AccountFeeResource($accountFee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountFee  $accountFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountFee $accountFee)
    {
        try {
            DB::beginTransaction();
            $accountFee->delete();
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();

            return response()->json([
                'message' => 'Xoá lệ phí tài khoản thất bại, vui lòng thử lại sau.'
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá lệ phí tài khoản thành công.'
        ], 200);
    }
}
