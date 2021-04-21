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
        'price',
        'buyable',
        'name',
        'description',

        'maximum_price',
        'minimum_price',
        'maximum_discount',
        'minimum_discount',
        'percentage_discount',
        'direct_discount',
        'usable_at',
        'usable_closed_at',
        'offered_at',
        'offer_closed_at',
    ];

    protected $casts = [
        'discount_code' => 'string',
        'price' => 'integer',
        'buyable' => 'boolean',
        'name' => 'string',
        'description' => 'string',

        'maximum_price' => 'integer',
        'minimum_price' => 'integer',
        'maximum_discount' => 'integer',
        'minimum_discount' => 'integer',
        'percentage_discount' => 'integer',
        'direct_discount' => 'integer',
        'usable_at' => 'timestamp',
        'usable_closed_at' => 'timestamp',
        'offered_at' => 'timestamp',
        'offer_closed_at' => 'timestamp',
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
        return $this->belongsToMany(Game::class, 'discount_code_supports_game', 'discount_code')
            ->withPivot('type_code');
    }
}
