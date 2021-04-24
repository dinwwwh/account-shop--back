<?php

namespace App\ModelTraits;

use App\Models\Permission;
use App\Helpers\ArgumentHelper;
use App\Helpers\PermissionHelper;

trait ManagePermissionInRole
{
    /**
     * Determine whether role has a permission
     *
     * @return boolean
     */
    public function hasPermissionTo($permission)
    {
        $permission = PermissionHelper::mustBePermission($permission);

        if (is_null($permission)) {
            return false;
        }

        $userPermissions = $this->_getRolePermissions();
        if ($userPermissions->contains($permission)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether role has one of these permissions
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
     * Determine whether role has these all permissions
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
     * give for role a permission or many permission
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
     * revoke these permission form role
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
     * Sync these permission form role
     *
     * @return void
     */
    public function syncPermissions(...$permissions)
    {

        $permissions = ArgumentHelper::firstOrAll($permissions);

        return $this->_syncPermissions($permissions);
    }

    /**
     * Return list permission role has
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function _getRolePermissions()
    {
        return $this->permissions;
    }

    /**
     * attach a permission form role
     *
     * @return void
     */
    public function _attachPermission($permission)
    {
        $permission = PermissionHelper::mustBePermission($permission);

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
        $permission = PermissionHelper::mustBePermission($permission);

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
        $permissions = PermissionHelper::mustBeManyPermissions($permissions);

        return $this->permissions()->sync($permissions);
    }
}
