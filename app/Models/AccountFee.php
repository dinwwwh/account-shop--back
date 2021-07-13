<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AccountFee extends Model implements Auditable
{
    use HasFactory,
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

    protected $fillable = [
        'maximum_cost',
        'minimum_cost',
        'maximum_fee',
        'minimum_fee',
        'percentage_cost',
        'direct_fee',
        'account_type_id',
    ];

    protected $casts = [
        'maximum_cost' => 'integer',
        'minimum_cost' => 'integer',
        'maximum_fee' => 'integer',
        'minimum_fee' => 'integer',
        'percentage_cost' => 'integer',
        'direct_fee' => 'integer',
        'account_type_id' => 'integer',
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
     * Relationship one-one with Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
