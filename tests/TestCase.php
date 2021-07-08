<?php

namespace Tests;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * To Fix error 419 in request to web middleware
     * Only use for laravel sanctum auth
     */
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $res = parent::json('get', '/sanctum/csrf-cookie');
        $cookies = $res->headers->getCookies();
        $XSRF_TOKEN = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $XSRF_TOKEN = $cookie->getValue();
            }
        }
        $headers = array_merge($headers, [
            'X-XSRF-TOKEN' => $XSRF_TOKEN,
        ]);
        return parent::json($method, $uri, $data, $headers);
    }

    /**
     * Make user with given permissions
     *
     * @param array $requiredPermissionKeys
     * @param mixed $userOrExcludedIds
     * @param bool $isStrictPermissions
     * @return \App\Models\User default user will has full permissions
     */
    static public $allPermissions;
    public function makeAuth(array $excludedPermissionKeys = [], $userOrExcludedIds = [], bool $isStrictPermissions = true): User
    {
        if (!optional(static::$allPermissions)->isNotEmpty()) {
            static::$allPermissions = Permission::all();
        }

        if ($userOrExcludedIds instanceof User) {
            $user = $userOrExcludedIds;
        } elseif (is_array($userOrExcludedIds)) {
            $user = User::whereNotIn('id', $userOrExcludedIds)->inRandomOrder()->first();
        } else {
            throw 'Third argument expect array or [App\Models\User]';
        }

        if ($isStrictPermissions) {
            $user->syncPermissions([]);
            $user->syncRoles([]);
        }
        $requiredPermissions = static::$allPermissions->filter(
            fn ($permission) => !in_array($permission->getKey(), $excludedPermissionKeys)
        );
        $user->givePermissionTo($requiredPermissions);
        return $user->refresh();
    }
}
