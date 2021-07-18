<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Validation\Rule as RuleHelper;

trait HelperForRule
{
    /**
     * Support create quickly a rule
     *
     * @return \App\Models\Rule
     */
    static public function createQuickly(array $data = null): Rule
    {
        $data = $data ?? [];
        $rule = static::create($data)->refresh();
        if ($rule->required) {
            $rule->unrequiredUsers()->sync($data['unrequiredUserIds'] ?? []);
        } else {
            $rule->requiredUsers()->sync($data['requiredUserIds'] ?? []);
        }
        return $rule;
    }

    /**
     * Support create quickly a rule
     *
     * @return \App\Models\Rule
     */
    public function updateQuickly(array $data): Rule
    {
        $this->update($data);
        if ($this->required) {
            $this->requiredUsers()->sync([]);
            $this->unrequiredUsers()->sync($data['unrequiredUserIds'] ?? []);
        } else {
            $this->requiredUsers()->sync($data['requiredUserIds'] ?? []);
            $this->unrequiredUsers()->sync([]);
        }
        return $this;
    }


    /**
     * Generate rule used to validate
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function generateRule(User $user): array
    {
        $isRequired = $this->isRequired($user);
        $rule = [];

        if ($isRequired) {
            $rule[] = 'required';
            $rule[] = 'min:1';
        } else {
            $rule[] = 'nullable';
        }

        $rule[] = 'array';
        $rule[] = 'max:' . $this->allowable_number;
        $rule['*'][] = $this->datatype;
        $rule['*'][] = 'distinct';

        if (!empty($this->min)) {
            $rule['*'][] = 'min:' . $this->min;
        }

        if (!empty($this->max)) {
            $rule['*'][] = 'max:' . $this->max;
        }

        if (!empty($this->allowable_values)) {
            $rule['*'][] = RuleHelper::in($this->allowable_values);
        }
        return $rule;
    }

    /**
     * Generate boolean rule used to validate
     * Just required true or false
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function generateBooleanRule(User $user): array
    {
        $isRequired = $this->isRequired($user);
        $rule = [];

        if ($isRequired) {
            $rule[] = 'required';
            $rule[] = 'boolean';
            $rule[] = RuleHelper::in([true]);
        } else {
            $rule[] = 'nullable';
            $rule[] = 'boolean';
        }

        return $rule;
    }

    /**
     * Determine whether $role must required this rule
     *
     * @param \App\Models\User $role
     * @return boolean
     */
    public function isRequired(User $user): bool
    {
        if ($this->required) {
            if (is_null($this->unrequiredUsers()->where('user_id', $user->getKey())->first())) {
                return true;
            } else {
                return false;
            }
        } else {
            if (is_null($this->requiredUsers()->where('user_id', $user->getKey())->first())) {
                return false;
            } else {
                return true;
            }
        }
    }
}
