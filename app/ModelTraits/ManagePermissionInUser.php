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
    private $_allUserPermissionKeys;
    public function hasPermissionTo($permission)
    {
        if (!$this->_allUserPermissionKeys) {
            $this->_allUserPermissionKeys = $this->getAllPermissions()->pluck('key')->toArray();
        }

        if ($permission instanceof Permission) {
            $permission = $permission->getKey();
        }

        return in_array($permission, $this->_allUserPermissionKeys);
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
        $userRoles = $this->roles()->with('permissions')->get();

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
