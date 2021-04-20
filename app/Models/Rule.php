<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rule as RuleHelper;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'placeholder',
        'datatype',
        'required',
        'multiple',
        'min',
        'max',
        'values'
    ];

    protected $casts = [
        'placeholder' => 'string',
        'datatype' => 'string',
        'required' => 'boolean',
        'multiple' => 'boolean',
        'min' => 'integer',
        'max' => 'integer',
        'values' => 'array',
    ];

    /**
     * To set default
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Custom
        static::creating(function ($query) {
            $query->placeholder = $query->placeholder ?? null;
            $query->datatype = $query->datatype ?? 'string';
            $query->required = $query->required ?? false;
            $query->multiple = $query->multiple ?? false;
            $query->min = $query->min ?? null;
            $query->max = $query->max ?? null;
            $query->values = $query->values ?? null;
        });

        static::updating(function ($query) {
            //
        });
    }

    /**
     * To make rule for validator
     *
     * @return void
     */
    public function make()
    {
        $result = $this->datatype;

        if (!empty($this->min)) {
            $result .= '|min:' . $this->min;
        }

        if (!empty($this->max)) {
            $result .= '|max:' . $this->max;
        }

        if (!empty($this->values)) {
            $result .= '|' . RuleHelper::in($this->values);
        }

        if (!empty($this->multiple)) {
            $parent = '';
            if ($this->required) {
                $parent .= 'required';
            } else {
                $parent .= 'nullable';
            }

            $result =  [
                'parent' => $parent . '|array',
                'children' => trim($result, '|'),
            ];
        } else {
            if ($this->required) {
                $result .= '|required';
            } else {
                $result .= '|nullable';
            }
        }

        return $result;
    }
}
