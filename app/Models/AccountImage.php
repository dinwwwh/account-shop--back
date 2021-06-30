<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\StorageFile;

class AccountImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'path'
    ];

    protected $casts = [
        'path' => StorageFile::class,
    ];

    /**
     * Relationship one-one with \App\Models\Account
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    function account()
    {
        return $this->belongsTo(Account::class);
    }
}
