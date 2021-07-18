<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\AccountInfo;
use App\Models\User;
use App\Rules\ValidateForKeys;
use Illuminate\Validation\Rule as ValidationRule;

trait ManageAccountInfoInAccountType
{

    /**
     * Generate rules to used validate account info's values
     *
     * @param \App\Models\User
     * @return array
     */
    public function generateAccountInfoRulesForValidation(User $user): array
    {
        $rules = [
            'rootRules' => [
                'nullable', 'array',
                new ValidateForKeys([
                    'integer',
                    ValidationRule::exists('App\Models\AccountInfo', 'id')
                        ->where('account_type_id', $this->getKey()),
                ])
            ],
        ];
        foreach ($this->accountInfos as $accountInfo) {
            $rules[$accountInfo->getKey()]
                = ['values' => $accountInfo->rule->generateRule($user)];
        }
        return $rules;
    }
}
