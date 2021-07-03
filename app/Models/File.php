<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\StorageFile;
use OwenIt\Auditing\Contracts\Auditable;

class File extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
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

    /**
     * Attributes should guarded
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'path' => StorageFile::class,
        'type' => 'string',
        'short_description' => 'string',
    ];

    /**
     * To boot model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // before model created
        static::creating(function ($query) {
            $query->creator_id = optional(auth()->user())->id;
        });
    }

    /**
     * Model owner of this file
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * User was created this file
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
