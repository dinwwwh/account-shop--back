<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Http\Requests\RechargePhonecard\StoreRequest;
use App\Http\Resources\RechargePhoneCardResource;
use App\Models\RechargePhonecard;
use DB;
use Exception;
use Http;
use Illuminate\Validation\Rule;
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
        $rechargePhonecards = RechargePhonecard::with($this->requiredModelRelationships)
            ->orderBy('id', 'DESC')
            ->paginate(15);

        return RechargePhoneCardResource::collection($rechargePhonecards);
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
            ->with($this->requiredModelRelationships)
            ->paginate(15);

        return RechargePhoneCardResource::collection($rechargePhonecard);
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

            switch ($request->port) {
                case config('recharge-phonecard.ports.thesieure'):
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post('http://thesieure.com/chargingws/v2', [
                        'telco' => $request->telco,
                        'code' => $request->code,
                        'serial' => $request->serial,
                        'amount' => $request->faceValue,
                        'request_id' => $rechargePhonecard->getKey(),
                        'partner_id' => config('recharge-phonecard.tsr_parent_id'),
                        'sign' => md5(config('recharge-phonecard.tsr_parent_key') . $request->code . $request->serial),
                        'command' => 'charging',
                    ])->json();

                    switch ($response['status']) {
                        case 1:
                            $rechargePhonecard->update([
                                'status' => config('recharge-phonecard.statuses.success'),
                                'real_face_value' => $response['value'],
                                'received_value' => $response['amount'],
                                'data' => $response,
                            ]);
                            break;
                        case 2:
                            $rechargePhonecard->update([
                                'status' => config('recharge-phonecard.statuses.invalid_face_value'),
                                'real_face_value' => $response['value'],
                                'received_value' => $response['amount'],
                                'data' => $response,
                            ]);
                            break;
                        case 99:
                            $rechargePhonecard->update([
                                'status' => config('recharge-phonecard.statuses.approving'),
                                'data' => $response,
                            ]);
                            break;
                        default:
                            $rechargePhonecard->update([
                                'status' => config('recharge-phonecard.statuses.error'),
                                'real_face_value' => 0,
                                'received_value' => 0,
                                'data' => $response,
                            ]);
                            break;
                    }
                    break;
            }

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
        $settingOfTelcos = config('recharge-phonecard.manual_telcos', []);
        $telco = collect($settingOfTelcos)
            ->where('key', $rechargePhonecard->telco)
            ->first();
        $faceValues = $telco['faceValues'];

        $request->validate([
            'status' => ['required', 'integer', Rule::in(config('recharge-phonecard.statuses'))],
            'realFaceValue' => [
                Rule::requiredIf($request->status == config('recharge-phonecard.statuses.invalid_face_value')),
                'integer',
                Rule::in(array_map(fn ($v) => $v['value'], $faceValues))
            ],
        ]);

        $faceValue = collect($faceValues)
            ->where('value', $rechargePhonecard->face_value)
            ->first();
        $tax = $faceValue['tax'];
        $taxForInvalidFaceValue = $faceValue['taxForInvalidFaceValue'];

        $receivedValue = 0;
        $realFaceValue = 0;
        $newStatus = config('recharge-phonecard.statuses.error');

        if ($request->status == config('recharge-phonecard.statuses.success')) {

            $realFaceValue = $rechargePhonecard->face_value;
            $taxValue = (int)($realFaceValue * $tax / 100);
            $taxValue = $taxValue > 0 ? $taxValue : 0;
            $receivedValue = $realFaceValue - $taxValue;
            $newStatus = $request->status;
        } elseif ($request->status == config('recharge-phonecard.statuses.invalid_face_value')) {

            $realFaceValue = $request->realFaceValue;
            $taxValue = (int)($realFaceValue * $taxForInvalidFaceValue / 100);
            $taxValue = $taxValue > 0 ? $taxValue : 0;
            $receivedValue = $realFaceValue - $taxValue;
            $newStatus = $request->status;
        }

        try {
            DB::beginTransaction();
            $rechargePhonecard->update([
                'status' => $newStatus,
                'real_face_value' => $realFaceValue,
                'received_value' => $receivedValue,
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
