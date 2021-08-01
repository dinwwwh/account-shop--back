<?php

namespace App\Http\Requests\RechargePhonecard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
    public function rules()
    {
        return [
            'telco' => ['required', 'string'],
            'serial' => ['required', 'string'],
            'code' => ['required', 'string'],
            'faceValue' => ['required', 'integer', Rule::in(config('recharge-phonecard.face-values', []))],
            'port' => ['required', 'integer', Rule::in(config('recharge-phonecard.ports', []))],
        ];
    }
}
