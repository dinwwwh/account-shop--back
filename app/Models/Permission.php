<?php

namespace App\Models;

use App\ModelTraits\HelperForPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Permission extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        HelperForPermission,
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

    /**
     * Get all users have directly this permission
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_permission');
    }

    /**
     * Get all roles have directly this permission
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
