<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\AccountAction;

trait ManageAccountActionInAccountType
{
    /**
     * Include account Actions model, user need performing to create game
     * Relationship many-many with Models\Role
     *
     * @param  App\Models\Role as $role
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function accountActionsThatRoleNeedFilling(Role $role)
    {
        return $role->belongsToMany(AccountAction::class, 'role_need_performing_account_action')
            ->where('account_type_id', $this->getKey())
            ->get();
    }
}
