<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'maximum_cost',
        'minimum_cost',
        'maximum_fee',
        'minimum_fee',
        'percentage_cost',
        'account_type_id',
    ];

    protected $casts = [
        'maximum_cost' => 'integer',
        'minimum_cost' => 'integer',
        'maximum_fee' => 'integer',
        'minimum_fee' => 'integer',
        'percentage_cost' => 'integer',
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
     * Relationship one-one with Models\AccountType
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
