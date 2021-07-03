<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\StorageFile;
use OwenIt\Auditing\Contracts\Auditable;

class AccountImage extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [];

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
