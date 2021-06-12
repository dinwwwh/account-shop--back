<?php

namespace App\ModelTraits;

use App\Models\Permission;
use App\Helpers\ArgumentHelper;

trait ManagePermissionInUser
{
    /**
     * Determine whether user has a permission
     *
     * @return boolean
     */
    public function hasPermissionTo($permission)
    {
        if (!($permission instanceof Permission)) {
            if (is_string($permission) || is_numeric($permission)) {
                $permission = Permission::find($permission);
            } else {
                return false;
            }
        }

        if (is_null($permission)) {
            return false;
        }

        $userPermissions = $this->getAllPermissions();

        return $userPermissions->contains($permission);
    }

    /**
     * Determine whether user has one of these permissions
     *
     * @return boolean
     */
    public function hasAnyPermission(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether user has these all permissions
     *
     * @return boolean
     */
    public function hasAllPermissions(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * give for user a permission or many permission
     *
     * @return void
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        foreach ($permissions as $permission) {
            $this->_attachPermission($permission);
        }

        return true;
    }

    /**
     * revoke these permission form user
     *
     * @return void
     */
    public function revokePermissionTo(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        foreach ($permissions as $permission) {
            $this->_detachPermission($permission);
        }

        return true;
    }

    /**
     * Sync these permission form user
     *
     * @return void
     */
    public function syncPermissions(...$permissions)
    {
        return $this->_syncPermissions($permissions);
    }

    /**
     * Return list permission user has
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        $userPermissions = $this->permissions;
        $userRoles = $this->roles;

        foreach ($userRoles as $role) {
            foreach ($role->permissions as $permission) {
                if (!$userPermissions->contains($permission)) {
                    $userPermissions->push($permission);
                }
            }
        }

        return $userPermissions;
    }

    /**
     * attach a permission form user
     *
     * @return void
     */
    public function _attachPermission($permission)
    {
        $permission = Permission::mustBePermission($permission);

        if (is_null($permission)) {
            return false;
        }

        $userPermissions = $this->permissions;
        if ($userPermissions->contains($permission)) {
            return true;
        }

        return $this->permissions()->attach($permission);
    }

    /**
     * Detach a permission form user
     *
     * @return boolean
     */
    protected function _detachPermission($permission)
    {
        $permission = Permission::mustBePermission($permission);

        if (is_null($permission)) {
            return true;
        }

        $userPermissions = $this->permissions;
        if (!$userPermissions->contains($permission)) {
            return true;
        }

        return $this->permissions()->detach($permission);
    }

    /**
     * Sync permissions form user
     *
     * @return boolean
     */
    protected function _syncPermissions(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        $permissions = Permission::mustBeManyPermissions($permissions);

        return $this->permissions()->sync($permissions);
    }
}
