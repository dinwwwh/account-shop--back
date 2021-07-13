<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManageAccountTypeInGame;
use App\ModelTraits\ManageGameInfoInGame;
use App\Observers\GameObserver;
use OwenIt\Auditing\Contracts\Auditable;

class Game extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        ManageAccountTypeInGame,
        \OwenIt\Auditing\Auditable,
        ManageGameInfoInGame;

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

    /**
     * Attributes should guarded
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'order' => 'integer',
        'publisher_name' => 'string',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
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
        static::observe(GameObserver::class);

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
     * Relationship one-many with AccountType.
     * Include account types this model has.
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }

    /**
     * Get all discount codes that it can use in this game
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function usableDiscountCodes()
    {
        return $this->morphToMany(
            DiscountCode::class,
            'model',
            'discount_code_supported_models',
        )
            ->withPivot('type_code')
            ->withTimestamps();
    }

    /**
     * Relationship one-many with Models\GameInfo
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function gameInfos()
    {
        return $this->hasMany(GameInfo::class);
    }
}
