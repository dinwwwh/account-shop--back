<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ModelTraits\HelperForRule;
use App\Rules\ValidateForKeys;
use OwenIt\Auditing\Contracts\Auditable;

class Rule extends Model implements Auditable
{
    use HasFactory,
        HelperForRule,
        \OwenIt\Auditing\Auditable;

    public static function getRequestRules()
    {
        return [
            'nullable',
            'array',
            'placeholder' => 'nullable|string',
            'datatype' => 'nullable|string',
            'required' => 'nullable|boolean',
            'allowable_number' => ['nullable', 'integer', 'min:1', 'max:255'],
            'min' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max' => ['nullable', 'integer', 'min:1', 'max:255'],
            'allowable_values' => ['nullable', 'array'],
            'allowable_values.*' => ['distinct'],
            'rawRequiredUsers' => ['nullable', 'array', new ValidateForKeys(['exists:users,id'])],
            'rawRequiredUsers.*' => ['array'],
            'rawUnrequiredUsers' => ['nullable', 'array',  new ValidateForKeys(['exists:users,id'])],
            'rawUnrequiredUsers.*' => ['array'],
        ];
    }

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
        'allowable_number',
        'min',
        'max',
        'allowable_values'
    ];

    protected $casts = [
        'placeholder' => 'string',
        'datatype' => 'string',
        'required' => 'boolean',
        'allowable_number' => 'integer',
        'min' => 'integer',
        'max' => 'integer',
        'allowable_values' => 'array',
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
            $query->allowable_number = $query->allowable_number ?? 1;

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
