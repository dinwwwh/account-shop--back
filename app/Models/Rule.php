<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    protected $casts = [
        'placeholder' => 'string',
        'type' => 'string',
        'datatype' => 'string',
        'required' => 'boolean',
        'multiple' => 'boolean',
        'min' => 'integer',
        'minlength' => 'integer',
        'max' => 'integer',
        'maxlength' => 'integer',
        'values' => 'array',
    ];

    /**
     * Function uses to store rule to database
     * Try to always store success regardless of initial value
     *
     * @param  mixed $data
     * @return Models\Rule or null
     */
    static public function tryStore($data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        $rule = static::create([
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

        return $rule->refresh();
    }

    /**
     * Function uses to store rule to database
     * Try to always store success regardless of initial value
     *
     * @param  mixed $data
     * @return Models\Rule or null
     */
    public function tryUpdate($data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        // Initial data
        $ruleData = [];
        foreach ($this->fillable as $key) {
            if (!is_null($data[Str::camel($key)] ?? null)) {
                $ruleData[$key] = $data[Str::camel($key)];
            }
        };

        $this->update($ruleData); // Save rule to database
        return $this;
    }
}
