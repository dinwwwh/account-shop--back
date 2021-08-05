<?php

namespace App\Http\Requests\RechargePhonecard;

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
            $settingOfTelcos = config('recharge-phonecard.manual_telcos', []);
            $validTelcoKeys = collect($settingOfTelcos)->map(fn ($telco) => $telco['key'])->toArray();

            $rulesOfTelco[] = Rule::in($validTelcoKeys);

            if (in_array($this->telco, $validTelcoKeys)) {
                $validFaceValues = collect($settingOfTelcos)
                    ->where('key', $this->telco)
                    ->first()['faceValues'];
                $validFaceValues = array_map(fn ($v) => $v['value'], $validFaceValues);
                $rulesOfFaceValue[] = Rule::in($validFaceValues);
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
