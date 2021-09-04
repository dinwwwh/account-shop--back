<?php

namespace App\Http\Controllers;

use App\Models\RechargePhonecard;
use Arr;
use Exception;
use Http;
use Illuminate\Http\Request;

class ThesieureController extends Controller
{

    /**
     * Get telcos of thesieure.com
     * Contain telco, faceValues, ...
     *
     */
    public function getTelcos()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get('http://thesieure.com/chargingws/v2/getfee', [
            'partner_id' => config('recharge-phonecard.tsr_parent_id'),
        ]);

        $objectTelcos = [];
        foreach ($response->json() as $item) {
            if (!array_key_exists($item['telco'], $objectTelcos)) {
                $objectTelcos[$item['telco']] = [];
            }
            $objectTelcos[$item['telco']][] = $item;
        }

        $telcos = [];
        foreach ($objectTelcos as $telcoName => $faceValues) {
            $telco = [
                'key' => $telcoName,
                'name' => $telcoName,
                'faceValues' => [],
            ];

            foreach ($faceValues as $faceValue) {
                $telco['faceValues'][] = [
                    'value' => $faceValue['value'],
                    'tax' => $faceValue['fees'],
                    'taxForInvalidFaceValue' => $faceValue['fees'] + $faceValue['penalty'],
                ];
            }
            $telcos[] = $telco;
        }

        return response()->json([
            'data' => $telcos,
        ]);
    }

    /**
     * Thesieure will callback about status card
     *
     */
    public function callback(Request $request)
    {
        abort_if(
            md5(
                config('recharge-phonecard.tsr_parent_key')
                    . $request->code
                    . $request->serial
            )
                != $request->callback_sign,
            403,
            'Do not have permission to access.'
        );

        $response = $request->all();
        $rechargePhonecard = RechargePhonecard::findOrFail($request->request_id);
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
        return response()->json([], 204);
    }
}
