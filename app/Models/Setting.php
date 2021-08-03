<?php

namespace App\Models;

use App\Casts\RulesCast;
use App\Helpers\ValidationHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Validator;

class Setting extends Model
{
    use HasFactory;

    protected $hidden = [];

    protected $fillable = [
        'key',
        'data',
        'rules_of_data',
        'structure_description',
        'description',
        'public',
    ];

    protected $casts = [
        'data' => 'array',
        'rules_of_data' => RulesCast::class,
        'public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
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

    static public function getValidatedOrFail(string $key, bool $full = false)
    {
        $setting = static::where('key', $key)->firstOrFail();

        $validator = Validator::make(
            [
                'data' =>  $setting->data
            ],
            ValidationHelper::parseRulesByArray('data', $setting->rules_of_data)
        );
        if (
            $validator->fails()
        ) {
            throw new ValidationException($validator);
        }

        return $full
            ? $setting
            : $setting->data;
    }
}
