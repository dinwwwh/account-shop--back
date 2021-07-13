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

    public const RULE_REQUEST = [
        'nullable',
        'array',
        'placeholder' => 'nullable|string',
        'datatype' => 'nullable|string',
        'required' => 'nullable|boolean',
        'multiple' => 'nullable|boolean',
        'min' => 'nullable|integer',
        'max' => 'nullable|integer',
        'values' => 'nullable|array',
        'requiredUserIds' => 'nullable|array',
        'requiredUserIds.*' => 'integer|exists:users,id',
        'unrequiredUserIds' => 'nullable|array',
        'unrequiredUserIds.*' => 'integer|exists:users,id'
    ];

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
    protected $with = ['requiredUsers', 'unrequiredUsers'];

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
            $query->datatype = $query->datatype ?? 'string';
            $query->required = $query->required ?? false;
            $query->multiple = $query->multiple ?? false;

            $query->creator_id = $query->creator_id ?? optional(auth()->user())->id;
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
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
     * Get all-users required do this rule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function requiredUsers()
    {
        return $this->belongsToMany(User::class, 'rule_user_required');
    }

    /**
     * Get all-users unrequired do this rule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unrequiredUsers()
    {
        return $this->belongsToMany(User::class, 'rule_user_unrequired');
    }
}
