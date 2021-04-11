<?php

namespace App\ModelTraits;

use App\Helpers\ParameterHelper;
use App\Helpers\RoleHelper;

trait ManageRoleInAccountType
{
    /**
     * give $role can use this permission to create account
     *
     * @return void
     */
    public function allowRole($role, $statusCode = null)
    {
        $role = RoleHelper::mustBeRole($role);
        $pivots = !is_null($statusCode) ? ['status_code' => $statusCode] : null;

        return $this->rolesCanUsedAccountType()->attach($role, $pivots);
    }

    /**
     * Determine whether $user can use this account type
     *
     * @return boolean
     */
    public function checkUserCanUse($user)
    {
        foreach ($user->roles as $userRole) {
            if ($this->rolesCanUsedAccountType->contains($userRole))
                return true;
        }

        return false;
    }
}
