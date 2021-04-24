<?php

namespace App\ModelTraits;

use App\Models\Role;
use App\Models\Permission;
use App\Helpers\ArgumentHelper;
use App\Helpers\RoleHelper;

trait ManageRoleInUser
{
    /**
     * Assign these roles for user
     *
     * @return void
     */
    public function assignRole(...$roles)
    {
        $roles = ArgumentHelper::firstOrAll($roles);

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
        $roles = ArgumentHelper::firstOrAll($roles);

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
        $roles = ArgumentHelper::firstOrAll($roles);

        return $this->_syncRoles($roles);
    }

    /**
     * Attach a role from user
     *
     * @return void
     */
    public function _attachRole($role)
    {
        $role = RoleHelper::mustBeRole($role);

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
        $role = RoleHelper::mustBeRole($role);

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
        $roles = ArgumentHelper::firstOrAll($roles);

        $roles = RoleHelper::mustBeManyRoles($roles);

        return $this->roles()->sync($roles);
    }
}
