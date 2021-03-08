<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
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
            'order' => 'nullable|integer',
            'publisherId' => 'nullable|integer',
            'name' => 'nullable|string',
            'image' => 'nullable|image',
            'roleIdsCanCreatedGame' => 'nullable|array',
            'roleIdsCanCreatedGame.*' => 'integer',
            'roleIdsCanCreatedGameMustNotApproving' => 'nullable|array',
            'roleIdsCanCreatedGameMustNotApproving.*' => 'integer',
        ];
    }
}
