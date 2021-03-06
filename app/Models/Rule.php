<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
