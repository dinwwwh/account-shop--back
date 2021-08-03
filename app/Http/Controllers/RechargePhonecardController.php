<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Http\Requests\RechargePhonecard\StoreRequest;
use App\Http\Resources\RechargePhoneCardResource;
use App\Models\RechargePhonecard;
use App\Models\Setting;
use DB;
use Illuminate\Http\Request;

class RechargePhonecardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Get approvable recharge phonecards.
     *
     * @return \Illuminate\Http\Response
     */
    public function getApprovable()
    {
        $rechargePhonecard = RechargePhonecard::where('status', config('recharge-phonecard.statuses.pending'))
            ->where('port', config('recharge-phonecard.ports.manual'))
            ->paginate(15);

        return RechargePhoneCardResource::withLoadMissingRelationships($rechargePhonecard);
    }

    /**
     * Recharge a newly recharge phonecard resource in storage.
     *
     * @param  App\Http\Requests\RechargePhonecard\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $data = ArrayHelper::convertArrayKeysToSnakeCase(
            $request->only(['telco', 'serial', 'code', 'faceValue', 'port'])
        );
        $data['status'] = config('recharge-phonecard.statuses.pending');

        try {
            DB::beginTransaction();

            $rechargePhonecard = RechargePhonecard::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return RechargePhoneCardResource::withLoadRelationships($rechargePhonecard);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return \Illuminate\Http\Response
     */
    public function show(RechargePhonecard $rechargePhonecard)
    {
        return RechargePhoneCardResource::withLoadMissingRelationships($rechargePhonecard);
    }

    /**
     * Start approve this $rechargePhonecard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return \Illuminate\Http\Response
     */
    public function startApproving(Request $request, RechargePhonecard $rechargePhonecard)
    {
        try {
            DB::beginTransaction();
            $rechargePhonecard->update([
                'status' => config('recharge-phonecard.statuses.approving'),
                'approver_id' => auth()->user()->getKey(),
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return RechargePhoneCardResource::withLoadMissingRelationships($rechargePhonecard);
    }

    /**
     * End approve this $rechargePhonecard.
     * Determine whether this $rechargePhonecard is success or error
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return \Illuminate\Http\Response
     */
    public function endApproving(Request $request, RechargePhonecard $rechargePhonecard)
    {
        $request->validate([
            'success' => ['required', 'boolean'],
        ]);

        $telcos = Setting::getValidatedOrFail('recharge_phonecard_manual_telcos');
        $newStatus = $request->success
            ? config('recharge-phonecard.statuses.success')
            : config('recharge-phonecard.statuses.error');
        $receivedValue = (int)($rechargePhonecard->face_value * $telcos[$rechargePhonecard->telco][$rechargePhonecard->face_value] / 100);

        try {
            DB::beginTransaction();
            $rechargePhonecard->update([
                'status' => $newStatus,
                'received_value' => $receivedValue
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RechargePhonecard $rechargePhonecard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RechargePhonecard  $rechargePhonecard
     * @return \Illuminate\Http\Response
     */
    public function destroy(RechargePhonecard $rechargePhonecard)
    {
        //
    }
}
