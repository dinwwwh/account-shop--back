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
}
