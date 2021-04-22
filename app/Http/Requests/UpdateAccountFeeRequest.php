<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountFeeRequest extends FormRequest
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
            'maximumCost' => 'nullable|integer',
            'minimumCost' => 'nullable|integer',
            'maximumFee' => 'nullable|integer',
            'minimumFee' => 'nullable|integer',
            'percentageCost' => 'nullable|integer',
            'directFee' => 'nullable|integer',
        ];
    }
}
