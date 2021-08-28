<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\HelperForFile;
use App\Observers\FileObserver;
use OwenIt\Auditing\Contracts\Auditable;

class File extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable,
        HelperForFile;

    /**
     * Group of type file
     * IMAGE = jpg, jpeg, png, bmp, gif, svg, or webp
     * AUDIO = audio files about music ...
     * ...
     *
     * @var string
     */
    public const IMAGE_TYPE = 'image';
    public const AUDIO_TYPE = 'audio';
    public const VIDEO_TYPE = 'video';
    public const APPLICATION_TYPE = 'application';

    /**
     * Used as a hint to differentiate representative image and other images
     *
     * @var string
     */
    public const SHORT_DESCRIPTION_OF_REPRESENTATIVE_IMAGE = 'REPRESENTATIVE_IMAGE';

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

    /**
     * Attributes should guarded
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * To boot model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(FileObserver::class);

        static::creating(function ($query) {
            $query->creator_id = $query->creator_id ?? optional(auth()->user())->id;
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });
    }

    /**
     * Model owner of this file
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * User was created this file
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
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
}
