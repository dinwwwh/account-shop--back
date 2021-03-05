<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order',
        'name',
        'slug',
        'description',
        'video_path',
        'required',
        'account_type_id',
        'last_updated_editor_id',
        'creator_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'video_path' => 'string',
        'required' => 'boolean',
        'account_type_id' => 'integer',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
    ];
}
