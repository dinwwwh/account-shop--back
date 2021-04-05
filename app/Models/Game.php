<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order', // nullable
        'publisher_name',
        'name',
        'slug',
        'image_path',
        'last_updated_editor_id',
        'creator_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'publisher_name' => 'string',
        'name' => 'string',
        'slug' => 'string',
        'image_path' => 'string',
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
    public function rolesCanCreatedGame()
    {
        return $this->belongsToMany(Role::class, 'role_can_created_game');
    }

    /**
     * Relationship one-many with AccountType.
     * Include account types this model has.
     *
     * @return void
     */
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }

    /**
     * Include infos account types
     * Relationship many-many with account type Models\Role
     * 1. Contain account types user can use it for create account
     * 2. ...
     * If $result is empty then return all account type
     * @return void
     */
    public function currentRoleCanUsedAccountTypes()
    {
        $result = new Collection;

        if (!auth()->check()) {
            return $result;
        }

        foreach (auth()->user()->roles as $role) {
            $accountTypes = $role->belongsToMany(AccountType::class, 'role_can_used_account_type')
                ->where('game_id', $this->id)
                ->get();
            foreach ($accountTypes as $accountType) {
                if (!$result->contains($accountType)) {
                    $result->push($accountType);
                }
            }

            # If It don't have any AccountType then treat all AccountType role can
            if ($result->isEmpty()) {
                return AccountType::where('game_id', $this->id)->get();
            }
        }
        return $result;
    }
}
