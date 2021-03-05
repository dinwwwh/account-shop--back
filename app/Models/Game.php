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
        'user_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'publisher_id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'image_path' => 'string',
        'user_id' => 'integer',
    ];
}
