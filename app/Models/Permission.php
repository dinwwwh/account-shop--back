<?php

namespace App\Models;

use App\ModelTraits\HelperForPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory,
        SoftDeletes,
        HelperForPermission;

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'name',
        'description',
        'parent_key',
    ];

    protected $casts = [
        'key' => 'string',
        'name' => 'string',
        'description' => 'string',
        'parent_key' => 'string',
    ];
}
