<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\Permission;

trait ManageRoleInUser
{
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
