<?php

namespace App\ModelTraits;

use App\Helpers\ArgumentHelper;
use Illuminate\Database\Eloquent\Collection;

trait HelperForPermission
{
    /**
     * Mutate first parameter to model permission
     *
     * @return null
     * or
     * @return App\Models\Permission
     */
    public static function mustBePermission($permission)
    {
        if (!($permission instanceof static)) {
            if (is_string($permission) || is_numeric($permission)) {
                $permission = static::find($permission);
            } else {
                return null;
            }
        }

        return $permission;
    }

    /**
     * Mutate parameters to model permissions
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function mustBeManyPermissions(...$permissions)
    {
        $permissions = ArgumentHelper::firstOrAll($permissions);

        $result = new Collection;
        foreach ($permissions as $permission) {
            $permission = static::mustBePermission($permission);

            if (!is_null($permission)) {
                $result->push($permission);
            }
        }

        return $result;
    }
}
