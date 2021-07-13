<?php

namespace App\Http\Requests\Account;

use App\Helpers\ValidationHelper;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGameInfosRequest extends FormRequest
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
        $account = $this->route('account');
        $user = auth()->user();

        return ValidationHelper::parseRulesByArray('gameInfos', $account->accountType->game->generateGameInfoRulesForValidation($user));
    }
}
