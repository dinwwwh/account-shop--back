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
        'status',
        'description',
        'representative_image_path',
        'game_id',
        'account_type_id',
        'censor_id',
        'last_updated_editor_id',
        'creator_id',
        'approved_at',
    ];

    protected $casts = [
        'username' => 'string',
        'password' => 'string',
        'price' => 'integer',
        'status' => 'integer',
        'description' => 'string',
        'representative_image_path' => 'string',
        'game_id' => 'integer',
        'account_type_id' => 'integer',
        'censor_id' => 'integer',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
        'approved_at' => 'timestamp',
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
     * @return void
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
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
     * Relationship one-one with User
     * Include infos of censor model
     *
     * @return void
     */
    public function censor()
    {
        return $this->belongsTo(User::class, 'censor_id');
    }
}
