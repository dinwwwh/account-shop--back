<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class StoreRuleRequest extends FormRequest
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
            'type' => 'required|string',
            'datatype' => 'required|string',
            'placeholder' => 'nullable|string',
            'required' => 'nullable|boolean',
            'multiple' => 'nullable|boolean',
            'min' => 'nullable|integer',
            'minlength' => 'nullable|integer',
            'max' => 'nullable|integer',
            'maxlength' => 'nullable|integer',
            'values' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            // 'email.required' => 'Email is required!',
        ];
    }

    public function attributes()
    {
        return [];
    }
}
