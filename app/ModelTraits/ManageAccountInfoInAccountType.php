<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\AccountInfo;

trait ManageAccountInfoInAccountType
{
    /**
     * Include account infos model user must filling to create game
     * Relationship many-many with Models\Role
     *
     * @param  App\Models\Role as $role
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function accountInfosThatRoleNeedPerforming(Role $role)
    {
        return $role->belongsToMany(AccountInfo::class, 'role_need_filling_account_info')
            ->where('account_type_id', $this->getKey())
            ->get();
    }
}
