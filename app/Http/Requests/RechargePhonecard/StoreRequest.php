<?php

namespace App\Http\Requests\RechargePhonecard;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Request;
use Validator;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $rulesOfTelco = ['required', 'string'];
        $rulesOfFaceValue = ['required', 'integer'];
        if ($this->port == config('recharge-phonecard.ports.manual')) {
            $manualTelcos = Setting::getValidatedOrFail('recharge_phonecard_manual_telcos');
            $validTelcos = array_keys($manualTelcos);
            $rulesOfTelco[] = Rule::in($validTelcos);

            if (in_array($this->telco, $validTelcos)) {
                $rulesOfFaceValue[] = Rule::in(array_keys($manualTelcos[$this->telco]));
            }
        }

        return [
            'telco' => $rulesOfTelco,
            'serial' => ['required', 'string'],
            'code' => ['required', 'string'],
            'faceValue' => $rulesOfFaceValue,
            'port' => ['required', 'integer', Rule::in(config('recharge-phonecard.ports', []))],
        ];
    }
}
