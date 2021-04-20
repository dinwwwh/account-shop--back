<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountCodeRequest extends FormRequest
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
            'discountCode' => 'required|string',
            'price' => 'nullable|integer',
            'buyable' => 'nullable|boolean',
            'name' => 'nullable|string',
            'description' => 'nullable|string',

            'maximumPrice' => 'nullable|integer',
            'minimumPrice' => 'nullable|integer',
            'maximumDiscount' => 'nullable|integer',
            'minimumDiscount' => 'nullable|integer',
            'percentageDiscount' => 'nullable|integer',
            'directDiscount' => 'nullable|integer',
            'usableAt' => 'nullable|date',
            'usableClosedAt' => 'nullable|date',
            'offeredAt' => 'nullable|date',
            'offerClosedAt' => 'nullable|date',
        ];
    }
}
