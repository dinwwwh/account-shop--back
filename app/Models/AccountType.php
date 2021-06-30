<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use App\ModelTraits\ManageRoleInAccountType;
use App\ModelTraits\ManageAccountInfoInAccountType;
use App\ModelTraits\ManageAccountActionInAccountType;
use App\ModelTraits\ManageAccountFeeInAccountType;


class AccountType extends Model
{
    use ManageRoleInAccountType,
        ManageAccountInfoInAccountType,
        ManageAccountActionInAccountType,
        ManageAccountFeeInAccountType,
        HasFactory,
        SoftDeletes;

    protected $fillable = [
        'order',
        'name',
        'slug',
        'description',
        'game_id',
        'last_updated_editor_id',
        'creator_id'
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'game_id' => 'integer',
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
     * Relationship one-one with Models\AccountInfo
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountInfos()
    {
        return $this->hasMany(AccountInfo::class);
    }

    /**
     * Relationship one-one with Models\AccountAction
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountActions()
    {
        return $this->hasMany(AccountAction::class);
    }

    /**
     * Relationship many-many with Models\Role
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function rolesCanUsedAccountType()
    {
        return $this->belongsToMany(Role::class, 'role_can_used_account_type')
            ->withPivot('status_code');
    }

    /**
     * Relationship one-one with Models\Game
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Relationship one-many with Models\AccountFee
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountFees()
    {
        return $this->hasMany(AccountFee::class);
    }

    /**
     * Relationship one-many with \App\Models\Account
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
