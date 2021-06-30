<?php

namespace App\ModelTraits;

use Illuminate\Validation\Rule as RuleHelper;
use App\Models\Role;

trait HelperForAccountAction
{
    /**
     * Generate rule used to validate
     *
     * @param \App\Models\Role $role
     * @return string
     */
    public function generateRule(Role $role): string
    {
        return $this->isRequired($role)
            ? 'required|boolean|' . RuleHelper::in(true)
            : 'nullable|boolean';
    }

    /**
     * Determine whether $role must required this account action
     *
     * @param \App\Models\Role $role
     * @return boolean
     */
    public function isRequired(Role $role): bool
    {
        if (!is_null($this->required)) {
            return $this->required;
        }

        return $this->requiredRoles->contains($role);
    }
}
