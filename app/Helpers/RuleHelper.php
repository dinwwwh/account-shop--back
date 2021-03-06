<?php

namespace App\Helpers;

use App\Models\Rule;
use DB;

class RuleHelper
{

    /**
     * Function uses to store rule to database
     * Try to always store success regardless of initial value
     *
     * @param  mixed $data
     * @return Models\Rule or false
     */
    static public function store($data)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $rule = Rule::create([
                'placeholder' => $data['placeholder'] ?? null,
                'type' => $data['type'] ?? 'text',
                'datatype' => $data['datatype'] ?? 'string',
                'required' => $data['required'] ?? false,
                'multiple' => $data['multiple'] ?? false,
                'min' => $data['min'] ?? null,
                'minlength' => $data['minlength'] ?? null,
                'max' => $data['max'] ?? null,
                'maxlength' => $data['maxlength'] ?? null,
                'values' => $data['values'] ?? null,
            ]); // Save rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return null;
        }

        return $rule->refresh();
    }

    /**
     * Function uses to update rule to database
     * Try to always update success regardless of initial value
     *
     * @return void
     */
    static function makeDataToUpdate($data)
    {
        // Initial data
        $ruleData = [];
        foreach ([
            'placeholder',
            'type',
            'datatype',
            'required',
            'multiple',
            'min',
            'minlength',
            'max',
            'maxlength',
            'values'
        ]
            as $key) {
            if (!empty($data[$key])) {
                $ruleData[$key] = $data[$key];
            }
        };

        return $ruleData;
    }
}
