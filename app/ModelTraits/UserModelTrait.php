<?php

namespace App\ModelTraits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

trait UserModelTrait
{

    /**
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * DIRECT PERMISSION FROM USER
     *
     */

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

        $userPermissions = $this->_getAllUserPermissions();

        return $userPermissions->contains($permission);
    }

    /**
     * Determine whether user has one of these permissions
     *
     * @return boolean
     */
    public function hasAnyPermission(...$permissions)
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

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
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

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
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

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
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

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
    public function _getAllUserPermissions()
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
        if (!($permission instanceof Permission)) {
            if (is_string($permission) || is_numeric($permission)) {
                $permission = Permission::find($permission);
            } else {
                return false;
            }
        }

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
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $key => $per) {
            if (!($per instanceof Permission)) {
                if (is_string($per) || is_numeric($per)) {
                    $per = Permission::find($per);
                } else {
                    break;
                }
            }

            if (is_null($per)) {
                break;
            } else {
                $permissions[$key] = $per;
            }
        }

        return $this->permissions()->sync($permissions);
    }

    /**
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * -------------------------------------------------------------------------------------------
     * DIRECT ROLE FROM USER
     *
     */

    /**
     * Assign these roles for user
     *
     * @return void
     */
    public function assignRole(...$roles)
    {
        if (is_array($roles[0])) {
            $roles = $roles[0];
        }

        foreach ($roles as $role) {
            $this->_attachRole($role);
        }

        return true;
    }

    /**
     * Remove these roles for user
     *
     * @return void
     */
    public function removeRole(...$roles)
    {
        if (is_array($roles[0])) {
            $roles = $roles[0];
        }

        foreach ($roles as $role) {
            $this->_detachRole($role);
        }

        return true;
    }

    /**
     * Sync these roles for user
     *
     * @return void
     */
    public function syncRoles(...$roles)
    {
        return $this->_syncRoles($roles);
    }

    /**
     * Attach a role from user
     *
     * @return void
     */
    public function _attachRole($role)
    {
        if (!($role instanceof Role)) {
            if (is_string($role) || is_numeric($role)) {
                $role = Permission::find($role);
            } else {
                return false;
            }
        }

        if (is_null($role)) {
            return false;
        }

        $userRoles = $this->roles;
        if ($userRoles->contains($role)) {
            return true;
        }

        return $this->roles()->attach($role);
    }

    /**
     * Detach a role from user
     *
     * @return void
     */
    public function _detachRole($role)
    {
        if (!($role instanceof Role)) {
            if (is_string($role) || is_numeric($role)) {
                $role = Permission::find($role);
            } else {
                return true;
            }
        }

        if (is_null($role)) {
            return true;
        }

        $userRoles = $this->roles;
        if ($userRoles->contains($role)) {
            return $this->roles()->detach($role);
        }

        return true;
    }

    /**
     * Sync role form user
     *
     * @return boolean
     */
    protected function _syncRoles(...$roles)
    {
        if (is_array($roles[0])) {
            $roles = $roles[0];
        }

        foreach ($roles as $key => $role) {
            if (!($role instanceof Role)) {
                if (is_string($role) || is_numeric($role)) {
                    $role = Role::find($role);
                } else {
                    break;
                }
            }

            if (is_null($role)) {
                break;
            } else {
                $roles[$key] = $role;
            }
        }

        return $this->roles()->sync($roles);
    }
}
