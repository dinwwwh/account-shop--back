<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManageAccountTypeInGame;
use OwenIt\Auditing\Contracts\Auditable;

class Game extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        ManageAccountTypeInGame,
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
        'order', // nullable
        'publisher_name',
        'name',
        'slug',
        'description',
        'image_path',
        'last_updated_editor_id',
        'creator_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'publisher_name' => 'string',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
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
