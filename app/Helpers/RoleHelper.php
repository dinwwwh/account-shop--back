<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleHelper
{
    /**
     * Mutate first parameter to model Role
     *
     * @return null
     * or
     * @return App\Models\Role
     */
    public static function mustBeRole($role)
    {
        if (!($role instanceof Role)) {
            if (is_string($role) || is_numeric($role)) {
                $role = Role::find($role);
            } else {
                return null;
            }
        }

        return $role;
    }

    /**
     * Mutate parameters to model Roles
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function mustBeManyRoles(...$roles)
    {
        $roles = ArgumentHelper::firstOrAll($roles);

        $result = new Collection;
        foreach ($roles as $role) {
            $role = static::mustBeRole($role);


            if (!is_null($role)) {
                $result->push($role);
            }
        }

        return $result;
    }
}
