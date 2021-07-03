<?php

namespace App\Models;

use App\ModelTraits\HelperForRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManagePermissionInRole;
use OwenIt\Auditing\Contracts\Auditable;


class Role extends Model implements Auditable
{
    use HasFactory,
        SoftDeletes,
        ManagePermissionInRole,
        HelperForRole,
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
        'name',
        'description',
        'style_classes',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'style_classes' => 'string',
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
     * Relationship many-many with Models\Permission
     * Include permissions user can do.
     *
     * @return void
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Relationship many-many with Models\AccountType
     * Include account types that role can used to create account.
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function accountTypes()
    {
        return $this->belongsToMany(AccountType::class, 'role_can_used_account_type')
            ->withPivot('status_code');
    }

    /**
     * Get all users had directly this roles
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_role');
    }
}
