<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountTypeRequest extends FormRequest
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
            'name' => 'nullable|string',
            'description' => 'nullable|string',

            'usableUsers' => 'nullable|array',
            'usableUsers.*' => 'array',
            'usableUsers.*.id' => 'required|integer|exists:users,id',
            'usableUsers.*.statusCode' => 'required|integer|' .  Rule::in(config('account.status_codes_after_created', [])),

            'approvableUsers' => 'nullable|array',
            'approvableUsers.*' => 'array',
            'approvableUsers.*.id' => 'required|integer|exists:users,id',
            'approvableUsers.*.statusCode' => 'required|integer|' .  Rule::in(config('account.status_codes_after_approved', [])),
        ];
    }
}
