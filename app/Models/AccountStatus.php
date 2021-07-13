<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class AccountStatus extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable;

    public const SHORT_DESCRIPTION_OF_CREATED = 'CREATED';
    public const SHORT_DESCRIPTION_OF_START_APPROVING = 'START_APPROVING';
    public const SHORT_DESCRIPTION_OF_END_APPROVING = 'END_APPROVING';
    public const SHORT_DESCRIPTION_OF_SOLD = 'SOLD';
    public const SHORT_DESCRIPTION_OF_CONFIRM_OK_BUY_BUYER = 'CONFIRM_OK_BUY_BUYER';
    public const SHORT_DESCRIPTION_OF_CONFIRM_NOT_OK_BUY_BUYER = 'CONFIRM_NOT_OK_BUY_BUYER';

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

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
     * Boot this model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Custom
        static::creating(function ($query) {
            $query->creator_id = $query->creator_id ?? optional(auth()->user())->id;
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });
    }

    /**
     * Get user was created this status code
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get latest user was updated this status code
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function latestUpdater()
    {
        return $this->belongsTo(User::class, 'latest_updater_id');
    }

    /**
     * Get account of this status code
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
