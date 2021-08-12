<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Http\Requests\Coupon\StoreRequest;
use App\Http\Requests\Coupon\UpdateRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use DB;
use Illuminate\Database\Eloquent\Builder;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::with($this->requiredModelRelationships)
            ->where(function (Builder $builder) {
                if ($this->keyword) {
                    $builder->where('code', 'LIKE', "%$this->keyword%")
                        ->whereOr('name', 'LIKE', "%$this->keyword%")
                        ->whereOr('description', 'LIKE', "%$this->keyword%");
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CouponResource::collection($coupons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $data = ArrayHelper::convertArrayKeysToSnakeCase($request->only(
            'code',
            'name',
            'description',
            'amount',

            'maximumValue',
            'minimumValue',
            'maximumDiscount',
            'minimumDiscount',
            'percentageDiscount',
            'directDiscount',
            'usableAt',
            'usableClosedAt',

            'price',
            'offeredAt',
            'offerClosedAt',
        ));

        try {
            DB::beginTransaction();

            $coupon = Coupon::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return CouponResource::withLoadRelationships($coupon);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        return CouponResource::withLoadMissingRelationships($coupon);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Coupon $coupon)
    {
        $data = ArrayHelper::convertArrayKeysToSnakeCase($request->only(
            'code',
            'name',
            'description',
            'amount',

            'maximumValue',
            'minimumValue',
            'maximumDiscount',
            'minimumDiscount',
            'percentageDiscount',
            'directDiscount',
            'usableAt',
            'usableClosedAt',

            'price',
            'offeredAt',
            'offerClosedAt',
        ));

        try {
            DB::beginTransaction();

            $coupon->update($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return CouponResource::withLoadRelationships($coupon);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {
        try {
            DB::beginTransaction();

            $coupon->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([], 204);
    }
}
