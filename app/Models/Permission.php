<?php

namespace App\Models;

use App\ModelTraits\HelperForPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Permission extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        HelperForPermission,
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

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'name',
        'description',
        'parent_key',
    ];

    protected $casts = [
        'key' => 'string',
        'name' => 'string',
        'description' => 'string',
        'parent_key' => 'string',
    ];

    /**
     * Boot model
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
     * Get all users have directly this permission
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_permission');
    }

    /**
     * Get all roles have directly this permission
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
