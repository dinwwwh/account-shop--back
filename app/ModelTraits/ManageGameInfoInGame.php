<?php

namespace App\ModelTraits;

use App\Models\User;
use App\Rules\ValidateForKeys;
use Illuminate\Validation\Rule as ValidationRule;

trait ManageGameInfoInGame
{

    /**
     * Generate rules to used validate game info's values
     *
     * @param \App\Models\User
     * @return array
     */
    public function generateGameInfoRulesForValidation(User $user): array
    {
        $rules = [
            'rootRules' => [
                'nullable', 'array',
                new ValidateForKeys([
                    'integer',
                    ValidationRule::exists('App\Models\GameInfo', 'id')
                        ->where('game_id', $this->getKey())
                ]),
            ],
        ];
        foreach ($this->gameInfos as $gameInfo) {
            $rules[$gameInfo->getKey()]
                = ['values' => $gameInfo->rule->generateRule($user)];
        }

        // Validate structure
        $rules['*.values'] = 'required';
        return $rules;
    }
}
