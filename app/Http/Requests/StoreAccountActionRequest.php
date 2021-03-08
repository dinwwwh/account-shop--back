<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountActionRequest extends FormRequest
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
            'accountTypeId' => 'required|integer',
            'order' => 'nullable|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'videoPath' => 'nullable|string',
            'required' => 'nullable|boolean',
            'roleIds' => 'required|array',
            'roleIds.*' => 'integer',
        ];
    }
}