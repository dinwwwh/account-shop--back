<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'path'
    ];

    protected $casts = [
        'path' => 'string',
    ];
}
