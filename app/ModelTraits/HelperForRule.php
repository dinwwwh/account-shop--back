<?php

namespace App\ModelTraits;

use Illuminate\Validation\Rule as RuleHelper;

trait HelperForRule
{
    /**
     * Generate rule used to validate
     *
     * @return array
     */
    public function generateRule()
    {
        $rule = [
            'nested' => $this->multiple,
            'rule' => '',
            'ruleOfParent' => '',
            'ruleOfChild' => '',
        ];

        if (!empty($this->min)) {
            $rule['rule'] .= '|min:' . $this->min;
        }

        if (!empty($this->max)) {
            $rule['rule'] .= '|max:' . $this->max;
        }

        if (!empty($this->values)) {
            $rule['rule'] .= '|' . RuleHelper::in($this->values);
        }

        if ($this->required) {
            $rule['rule'] .= '|required';
        } else {
            $rule['rule'] .= '|nullable';
        }

        if ($this->multiple) {
            $parent = '';
            if ($this->required) {
                $parent .= 'required';
            } else {
                $parent .= 'nullable';
            }

            $rule['ruleOfParent'] = $parent . '|array';
            $rule['ruleOfChild'] =  trim($rule['rule'], '| ');
        }

        return $rule;
    }
}
