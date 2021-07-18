<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\AccountAction;
use App\Models\User;
use App\Rules\ValidateForKeys;
use Illuminate\Validation\Rule as ValidationRule;

trait ManageAccountActionInAccountType
{

    /**
     * Generate rules to used validate account action's values
     *
     * @param \App\Models\User
     * @return array
     */
    public function generateAccountActionRulesForValidation(User $user): array
    {
        $rules = [
            'rootRules' => [
                'nullable', 'array',
                new ValidateForKeys([
                    'integer',
                    ValidationRule::exists('App\Models\AccountAction', 'id')
                        ->where('account_type_id', $this->getKey())
                ]),
            ],
        ];
        foreach ($this->accountActions as $accountAction) {
            $rules[$accountAction->getKey()]
                = ['isDone' => $accountAction->rule->generateBooleanRule($user)];
        }
        return $rules;
    }
}
