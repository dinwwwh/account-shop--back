<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ModelTraits\HelperForDiscountCode;

class DiscountCode extends Model
{
    use HasFactory,
        HelperForDiscountCode;

    protected $primaryKey = 'discount_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'discount_code',
        'type_code',
        'maximum_price',
        'minimum_price',
        'maximum_discount',
        'minimum_discount',
        'percentage_discount',
        'direct_discount',
    ];

    protected $casts = [
        'discount_code' => 'string',
        'type_code' => 'integer',
        'maximum_price' => 'integer',
        'minimum_price' => 'integer',
        'maximum_discount' => 'integer',
        'minimum_discount' => 'integer',
        'percentage_discount' => 'integer',
        'direct_discount' => 'integer',
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
     * Relationship many-many with Game
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function supportedGames()
    {
        return $this->belongsToMany(Game::class, 'discount_code_supports_game')
            ->withPivot('style_code');
    }
}
