<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ModelTraits\HelperForRule;

class Rule extends Model
{
    use HasFactory, HelperForRule;

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
     * Relationship many-many with Models\Role
     * Include roles must required in this rule
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function requiredRoles()
    {
        return $this->belongsToMany(Role::class, 'rule_required_roles');
    }
}
