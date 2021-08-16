<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManageAccountFeeInAccount;
use App\ModelTraits\ManagePriceInAccount;
use App\Observers\AccountObserver;
use App\Models\Pivot\AccountAccountAction;
use App\Models\Pivot\AccountAccountInfo;
use App\Models\Pivot\AccountHasGameInfos;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Redactors\RightRedactor;


class Account extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        ManageAccountFeeInAccount,
        ManagePriceInAccount,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'accountActions', #Contain all info account actions of this model
        'accountInfos', #Contain all account infos of this model
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [
        'password' => RightRedactor::class,
    ];

    protected $with = ['accountType.accountFees', 'latestAccountStatus'];

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
    protected $casts = [
        'username' => 'string',
        'password' => 'string',
        'cost' => 'integer',
        'description' => 'string',
        'account_type_id' => 'integer',
        'approver_id' => 'integer',
        'buyer_id' => 'integer',
        'sold_at_price' => 'integer',
        'sold_at' => 'datetime',
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
        static::observe(AccountObserver::class);

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
     * Get  representative image of this account
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function representativeImage()
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type', File::IMAGE_TYPE)
            ->where('short_description', File::SHORT_DESCRIPTION_OF_REPRESENTATIVE_IMAGE);
    }

    /**
     * Get images of this account (exclude representative image)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function otherImages()
    {
        return $this->images()->where('short_description', null);
    }

    /**
     * Get all-images of this account (include representative image)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type', File::IMAGE_TYPE);
    }

    /**
     * Relationship many-one with Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Relationship one-one with User
     * Include infos of model creator
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship one-one with User
     * Include infos of editor last updated model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function latestUpdater()
    {
        return $this->belongsTo(User::class, 'latest_updater_id');
    }

    /**
     * Get latest status_code of this account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestAccountStatus()
    {
        return $this->hasOne(AccountStatus::class)->latestOfMany();
    }

    /**
     * Get all status_codes of this account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accountStatuses()
    {
        return $this->hasMany(AccountStatus::class);
    }

    /**
     * Relationship one-one with User
     * Include infos of buyer model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relationship many-many with Models\AccountInfo
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountInfos()
    {
        return $this->belongsToMany(AccountInfo::class, 'account_account_info')
            ->using(AccountAccountInfo::class)
            ->withPivot(['values', 'id'])
            ->withTimestamps();
    }

    /**
     * Relationship many-many with Models\AccountAction
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountActions()
    {
        return $this->belongsToMany(AccountAction::class, 'account_account_action')
            ->using(AccountAccountAction::class)
            ->withPivot(['is_done', 'id'])
            ->withTimestamps();
    }

    /**
     * Relationship many-many with Models\GameInfo
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function gameInfos()
    {
        return $this->belongsToMany(GameInfo::class, 'account_has_game_infos')
            ->using(AccountHasGameInfos::class)
            ->withPivot(['values', 'id'])
            ->withTimestamps();
    }
}
