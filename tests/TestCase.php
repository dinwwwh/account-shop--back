<?php

namespace Tests;

use App\Models\Permission;
use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // DB::rollBack();
        // DB::beginTransaction();
        // $this->seed(\Database\Seeders\ForTestSeeder::class);
    }

    /**
     * Run this method per end of classes
     */
    public function test_prepare()
    {
        $this->refreshDatabase();
        $this->assertTrue(true);
    }

    public function refreshDatabase(): void
    {
        $this->seed(\Database\Seeders\ForTestSeeder::class);
    }

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
    public function makeAuth(array $excludedPermissionKeys = [], $userOrExcludedIds = [], bool $isRevertPermissions = false): User
    {
        if (!optional(static::$allPermissions)->isNotEmpty()) {
            static::$allPermissions = Permission::all();
        }

        if ($userOrExcludedIds instanceof User) {
            $user = $userOrExcludedIds;
        } elseif (is_array($userOrExcludedIds)) {
            $user = User::whereNotIn('id', $userOrExcludedIds)->inRandomOrder()->first();
        } else {
            throw 'Third argument expect array or [App\Models\User] , ' . $userOrExcludedIds . ' given.';
        }

        $user->syncRoles([]);
        if ($isRevertPermissions) {
            $user->syncPermissions($excludedPermissionKeys);
        } else {
            $requiredPermissions = static::$allPermissions->filter(
                fn ($permission) => !in_array($permission->getKey(), $excludedPermissionKeys)
            );
            $user->syncPermissions($requiredPermissions);
        }
        return $user->refresh();
    }
}
