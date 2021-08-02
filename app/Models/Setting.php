<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $hidden = [];

    protected $fillable = [
        'key',
        'data',
    ];

    protected $casts = [
        'data' => 'json'
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

    static public function getAndMerge(string $key, $idealValue = null)
    {
        $setting = static::where('key', $key)->first();

        if (is_null($setting)) return $idealValue;

        if (!is_array($idealValue)) return $setting;

        if (!is_array($setting)) return $idealValue;

        return array_merge($idealValue,  $setting);
    }
}
