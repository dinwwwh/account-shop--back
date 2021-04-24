<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class ManageRoleInUserTest extends TestCase
{
    public function test_assignRole()
    {
        $user = User::inRandomOrder()->first();

        # one role
        $role = Role::inRandomOrder()->first();
        $user->assignRole($role);
        $this->assertDatabaseHas('user_has_role', [
            'user_id' => $user->getKey(),
            'role_key' => $role->getKey(),
        ]);

        # many role
        $roles = Role::inRandomOrder()->limit(rand(2, 10))->get();
        $user->assignRole($roles);
        foreach ($roles as $role) {
            $this->assertDatabaseHas('user_has_role', [
                'user_id' => $user->getKey(),
                'role_key' => $role->getKey(),
            ]);
        }
    }

    public function test_removeRole()
    {
        $user = User::inRandomOrder()->first();

        # one role
        $role = Role::inRandomOrder()->first();
        $user->removeRole($role);
        $this->assertDatabaseMissing('user_has_role', [
            'user_id' => $user->getKey(),
            'role_key' => $role->getKey(),
        ]);

        # many role
        $roles = Role::limit(2)->get();
        $user->removeRole($roles);
        foreach ($roles as $role) {
            $this->assertDatabaseMissing('user_has_role', [
                'user_id' => $user->getKey(),
                'role_key' => $role->getKey(),
            ]);
        }
    }

    public function test_syncRoles()
    {
        $user = User::inRandomOrder()->first();

        # one role
        $role = Role::inRandomOrder()->first();
        $user->syncRoles($role);
        $this->assertDatabaseHas('user_has_role', [
            'user_id' => $user->getKey(),
            'role_key' => $role->getKey(),
        ]);

        # many role
        $roles = Role::inRandomOrder()->limit(rand(2, 10))->get();
        $user->syncRoles($roles);
        foreach ($roles as $role) {
            $this->assertDatabaseHas('user_has_role', [
                'user_id' => $user->getKey(),
                'role_key' => $role->getKey(),
            ]);
        }

        # no role
        $roles = [];
        $user->syncRoles($roles);

        $roles = Role::all();
        foreach ($roles as $role) {
            $this->assertDatabaseMissing('user_has_role', [
                'user_id' => $user->getKey(),
                'role_key' => $role->getKey(),
            ]);
        }
    }

    public function test_carefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->test_assignRole();
            $this->test_removeRole();
            $this->test_syncRoles();
        }
    }
}
