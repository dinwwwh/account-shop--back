<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\HelperForAccountAction;

class AccountAction extends Model
{
    use HasFactory,
        SoftDeletes,
        HelperForAccountAction;

    protected $fillable = [
        'order',
        'name',
        'slug',
        'description',
        'video_path',
        'required',
        'display_type',
        'account_type_id',
        'last_updated_editor_id',
        'creator_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'video_path' => 'string',
        'required' => 'boolean',
        'display_type' => 'integer',
        'account_type_id' => 'integer',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
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
            $query->creator_id = auth()->user()->id;
            $query->last_updated_editor_id = auth()->user()->id;
        });

        static::updating(function ($query) {
            $query->last_updated_editor_id = auth()->user()->id;
        });
    }

    /**
     * Relationship one-one with User
     * Include infos of model creator
     *
     * @return void
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship one-one with User
     * Include infos of editor last updated model
     *
     * @return void
     */
    public function lastUpdatedEditor()
    {
        return $this->belongsTo(User::class, 'last_updated_editor_id');
    }

    /**
     * Relationship many-many with Models\Role
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function requiredRoles()
    {
        return $this->belongsToMany(Role::class, 'account_action_required_roles');
    }
}
