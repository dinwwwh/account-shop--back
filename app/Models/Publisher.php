<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'last_updated_editor_id',
        'creator_id'
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'last_updated_editor_id' => 'integer',
        'creator_id' => 'integer',
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
     * Relationship one-many with AccountType.
     * Include account types this model has.
     *
     * @return void
     */
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }


    /**
     * Include infos account types
     * Relationship many-many with account type Models\Role
     * 1. Contain account types user can use it for create account
     * 2. ...
     * If $result is empty then return all account type
     * @return void
     */
    public function currentRoleCanUsedAccountTypes()
    {
        $result =  auth()->user()->role->belongsToMany(AccountType::class, 'role_can_used_account_type')
            ->where('publisher_id', $this->id)
            ->get();
        if ($result->isEmpty()) {
            $result = AccountType::where('publisher_id', $this->id)->get();
        }
        return $result;
    }
}
