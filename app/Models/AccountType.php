<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use App\ModelTraits\ManageAccountInfoInAccountType;
use App\ModelTraits\ManageAccountActionInAccountType;
use App\ModelTraits\ManageAccountFeeInAccountType;
use App\ModelTraits\ManageUserInAccountType;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use OwenIt\Auditing\Contracts\Auditable;


class AccountType extends Model implements Auditable
{
    use
        ManageAccountInfoInAccountType,
        ManageAccountActionInAccountType,
        ManageAccountFeeInAccountType,
        HasFactory,
        SoftDeletes,
        \OwenIt\Auditing\Auditable,
        ManageUserInAccountType;

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
        'order',
        'name',
        'slug',
        'description',
        'game_id',
        'latest_updater_id',
        'creator_id'
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'game_id' => 'integer',
        'latest_updater_id' => 'integer',
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
            $query->creator_id = $query->creator_id ?? optional(auth()->user())->id;
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
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
    public function latestUpdater()
    {
        return $this->belongsTo(User::class, 'latest_updater_id');
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
     * Get users can use this account type to create account
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usableUsers()
    {
        return $this->belongsToMany(User::class, 'account_type_user_usable')
            ->withPivot('status_code');
    }

    /**
     * Get users that it can approve account created by this account type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function approvableUsers()
    {
        return $this->belongsToMany(User::class, 'account_type_user_approvable')
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

    /**
     * Get coupons can use for account of this account type
     *
     */
    public function coupons(): MorphToMany
    {
        return $this->morphToMany(Coupon::class, 'couponnable')
            ->withTimestamps();
    }
}
