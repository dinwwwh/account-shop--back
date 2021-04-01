<?php

namespace App\ModelTraits;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Permission;

trait UserModelTrait
{
    /**
     * Determine whether user has a permission
     *
     * @return boolean
     */
    public function hasPermission($permission)
    {
        if (!$permission instanceof Permission) {
            $permission = Permission::find($permission);
        }

        if (is_null($permission)) {
            return false;
        }

        $listPermission = $this->getListPermission();
        foreach ($listPermission as $i) {
            if ($permission->key === $i->key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return list permission user has
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getListPermission()
    {
        $userRoles = $this->roles;
        $userPermissions = $this->permissions;
        $listPermission = new Collection();

        foreach ($userRoles as $role) {
            foreach ($role->permissions as $permission) {
                if (!$listPermission->contains($permission)) {
                    $listPermission->push($permission);
                }
            }
        }

        foreach ($userPermissions as $permission) {
            if (!$listPermission->contains($permission)) {
                $listPermission->push($permission);
            }
        }

        return $listPermission;
    }

    /**
     * give user a permission
     *
     * @return boolean
     */
    public function allow($permission)
    {
        if (!$permission instanceof Permission) {
            $permission = Permission::find($permission);
        }

        if (is_null($permission)) {
            return false;
        }

        return $this->permissions()->attach($permission);
    }

    /**
     * Detach a permission form user
     *
     * @return boolean
     */
    public function detach($permission)
    {
        if (!$permission instanceof Permission) {
            $permission = Permission::find($permission);
        }

        if (is_null($permission)) {
            return false;
        }

        return $this->permissions()->detach($permission);
    }

    /**
     * Sync allows form user
     *
     * @return boolean
     */
    public function syncAllow(...$permissions)
    {
        foreach ($permissions as $key => $per) {
            $permission = null;
            if (!$per instanceof Permission) {
                $permission = Permission::find($per);
            }

            if (is_null($permission)) {
                return false;
            } else {
                $permissions[$key] = $permission;
            }
        }

        return $this->permissions()->sync($permissions);
    }
}
