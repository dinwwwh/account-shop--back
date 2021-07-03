<?php

namespace App\Models;

use App\PivotModels\AccountHasGameInfos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;


class AccountInfo extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
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
        'order',
        'name',
        'slug',
        'description',
        'rule_id',
        'account_type_id',
        'last_updated_editor_id',
        'creator_id'
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'rule_id' => 'integer',
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
     * Relationship one-one with rule model
     *
     * @return void
     */
    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

    /**
     * Relationship one-one with \App\Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Relationship one-one with \App\Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_has_game_infos')
            ->using(AccountHasGameInfos::class)
            ->withPivot('value')
            ->withTimestamps();
    }
}
