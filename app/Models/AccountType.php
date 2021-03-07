<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AccountType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order',
        'name',
        'slug',
        'description',
        'publisher_id',
        'last_updated_editor_id',
        'creator_id'
    ];

    protected $casts = [
        'order' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'publisher_id' => 'integer',
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
     * Include account infos model user must filling to create game
     * Relationship many-many with Models\Role
     *
     * @param  mixed $game
     * @return void
     */
    public function currentRoleNeedFillingAccountInfos()
    {
        $result = auth()->user()->role->belongsToMany(AccountInfo::class, 'role_must_filling_account_info')
            ->where('account_type_id', $this->id)
            ->get();
        return $result;
    }

    /**
     * Include account Actions model, user need performing to create game
     * Relationship many-many with Models\Role
     *
     * @param  mixed $game
     * @return void
     */
    public function  currentRoleNeedPerformingAccountActions()
    {
        $result = auth()->user()->role->belongsToMany(AccountAction::class, 'role_must_performing_account_action')
            ->where('account_type_id', $this->id)
            ->get();
        return $result;
    }
}
