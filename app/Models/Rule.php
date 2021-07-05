<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ModelTraits\HelperForRule;
use OwenIt\Auditing\Contracts\Auditable;

class Rule extends Model implements Auditable
{
    use HasFactory,
        HelperForRule,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [];

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
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['requiredRoles'];

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
            // $query->required = $query->required ?? false;
            $query->multiple = $query->multiple ?? false;
            $query->min = $query->min ?? null;
            $query->max = $query->max ?? null;
            $query->values = $query->values ?? null;

            $query->creator_id = optional(auth()->user())->id;
            $query->latest_updater_id = optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = optional(auth()->user())->id;
        });
    }

    /**
     * Get user was created this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get user was updated latest this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function latestUpdater()
    {
        return $this->belongsTo(User::class, 'latest_updater_id');
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
