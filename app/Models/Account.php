<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'price',
        'status_code',
        'description',
        'representative_image_path',
        'game_id',
        'account_type_id',
        'censor_id',
        'buyer_id',
        'sold_at_price',
        'sold_at',
        'last_updated_editor_id',
        'creator_id',
        'last_role_key_creator_used',
        'approved_at',
    ];

    protected $casts = [
        'username' => 'string',
        'password' => 'string',
        'price' => 'integer',
        'status_code' => 'integer',
        'description' => 'string',
        'representative_image_path' => 'string',
        'game_id' => 'integer',
        'account_type_id' => 'integer',
        'censor_id' => 'integer',
        'buyer_id' => 'integer',
        'sold_at_price' => 'integer',
        'sold_at' => 'timestamp',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
        'last_role_key_creator_used' => 'string',
        'approved_at' => 'timestamp',

        // Pivot can't work
        // 'value' => 'array',
        'account_actions.pivot.value' => 'array',
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
     * Relationship one-many with Models\AccountImage
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function images()
    {
        return $this->hasMany(AccountImage::class);
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
     * Relationship one-one with Models\AccountType
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
    public function lastUpdatedEditor()
    {
        return $this->belongsTo(User::class, 'last_updated_editor_id');
    }

    /**
     * Relationship one-one with User
     * Include infos of censor model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function censor()
    {
        return $this->belongsTo(User::class, 'censor_id');
    }

    /**
     * Relationship many-many with Models\AccountInfo
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function infos()
    {
        return $this->belongsToMany(AccountInfo::class, 'account_account_info')
            ->withPivot('value');
    }

    /**
     * Relationship many-many with Models\AccountAction
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function actions()
    {
        return $this->belongsToMany(AccountAction::class, 'account_account_action')
            ->withPivot('value');
    }

    /**
     * Relationship one-one with Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function type()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }
}
