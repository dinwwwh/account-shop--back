<?php

namespace App\Http\Requests\Account;

use App\Helpers\ValidationHelper;
use App\Models\AccountType;
use Illuminate\Foundation\Http\FormRequest;

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
     * Prepare for validation run before validation $ rules method
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // $this->merge([
        //     'accountInfo_ids' => array_keys($this->get('accountInfos', [])),
        //     'accountAction_ids' => array_keys($this->get('accountActions', [])),
        //     'gameInfos_id' => array_keys($this->get('gameInfos', [])),
        // ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $accountType = $this->route('accountType');
        $user = auth()->user();

        return array_merge(
            [
                'username' => 'required|string',
                'password' => 'required|string',
                'cost' => 'required|integer',
                'description' => 'nullable|string',
                'representativeImage' => 'required|image',
                'images' => 'nullable|array',
                'images.*' => 'image',
            ],
            ValidationHelper::parseRulesByArray('rawAccountInfos', $accountType->generateAccountInfoRulesForValidation($user)),
            ValidationHelper::parseRulesByArray('rawAccountActions', $accountType->generateAccountActionRulesForValidation($user)),
            ValidationHelper::parseRulesByArray('rawGameInfos', $accountType->game->generateGameInfoRulesForValidation($user)),
        );
    }
}
