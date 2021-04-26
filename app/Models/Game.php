<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManageAccountTypeInGame;

class Game extends Model
{
    use HasFactory, SoftDeletes, ManageAccountTypeInGame;

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
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }

    /**
     * Relationship many-many with DiscountCode
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function supportedDiscountCodes()
    {
        return $this->belongsToMany(DiscountCode::class, 'discount_code_supports_game', null, 'discount_code')
            ->withPivot('type_code');
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
