<?php

namespace App\ModelTraits;

use Illuminate\Validation\Rule as RuleHelper;

trait HelperForAccountAction
{
    /**
     * Generate rule used to validate
     *
     * @return string
     */
    public function generateRule(): string
    {
        return $this->required
            ? 'required|' . RuleHelper::in(true)
            : 'nullable|boolean';
    }
}
