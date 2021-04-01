<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\ModelTraits\UserModelTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Include info roles model
     * Relationship many-many with rule model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_role');
    }

    /**
     * Relationship many-many with permission model
     *
     * @return Illuminate\Database\Eloquent\Factories\Relationship
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_has_permission');
    }
}
