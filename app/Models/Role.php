<?php

namespace App\Models;

use App\ModelTraits\HelperForRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ModelTraits\ManagePermissionInRole;


class Role extends Model
{
    use HasFactory,
        SoftDeletes,
        ManagePermissionInRole,
        HelperForRole;


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
     * Include games that role can created
     *
     * @return void
     */
    public function canCreatedGame()
    {
        $result = $this->belongsToMany(Game::class, 'role_can_created_game')
            ->get();
        return $result;
    }

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
}
