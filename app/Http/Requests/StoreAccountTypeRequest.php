<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTypeRequest extends FormRequest
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
            'publisherId' => 'required|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'roleIds' => 'nullable|array',
            'roleIds.*' => 'integer',
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
        return [
            // 'name' => 'tên',
        ];
    }
}
