<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\ModelTraits\ManagePermissionInUser;
use App\ModelTraits\ManageRoleInUser;
use App\ModelTraits\ManageCoinInUser;
use App\Notifications\ResetPasswordNotification;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Redactors\RightRedactor;


class User extends Authenticatable implements Auditable
{
    use HasFactory,
        Notifiable,
        ManagePermissionInUser,
        ManageRoleInUser,
        ManageCoinInUser,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'email',
        'remember_token', #Token remember user in client
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
        'remember_token' => RightRedactor::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'gold_coin',
        'silver_coin',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'gold_coin' => 'integer',
        'silver_coin' => 'integer',

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
     * Include info roles model
     * Relationship many-many with rule model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_role');
    }

    /**
     * Relationship many-many with permission model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_has_permission');
    }

    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Relationship many-many with \Models\Account
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'creator_id');
    }

    /**
     * Get account types that this user can use it to create account
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usableAccountTypes()
    {
        return $this->belongsToMany(AccountType::class, 'account_type_user_usable')
            ->withPivot('status_code');
    }

    /**
     * Get account types that user
     * can approve account created by it
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function approvableAccountTypes()
    {
        return $this->belongsToMany(AccountType::class, 'account_type_user_approvable')
            ->withPivot('status_code');
    }
}
