<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order', // nullable
        'publisher_id',
        'name',
        'slug',
        'image_path',
        'last_updated_editor_id',
        'creator_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'publisher_id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'image_path' => 'string',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
    ];

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
     * Relationship one-one belong to Models\Publisher
     *
     * @return void
     */
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
}
