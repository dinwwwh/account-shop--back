<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->makeBaseRole();
        $this->giveAllPermissionsForAdminRole();
    }

    /**
     * Make base roles
     *
     */
    public function makeBaseRole()
    {
        $admin = Role::firstOrCreate(
            [
                'key' => 'administrator',
            ],
            [
                'key' => 'administrator',
                'name' => 'quản lý hệ thống',
                'description' => 'Có những quyền cao nhất.'
            ]
        );

        $guest = Role::firstOrCreate(
            [
                'key' => 'customer',
            ],
            [
                'key' => 'customer',
                'name' => 'khách hàng',
                'description' => 'Có những quyền cơ bản nhất.'
            ]
        );

        $tester = Role::firstOrCreate(
            [
                'key' => 'tester',
            ],
            [
                'key' => 'tester',
                'name' => 'kiểm thử',
                'description' => 'Thường là máy và không không nên sử dụng vai trò này cho bất cứ ai.'
            ]
        );
    }

    public function giveAllPermissionsForAdminRole()
    {
        $permissions = [];
        foreach (Permission::all() as $permission) {
            $permissions[] = $permission;
        }
        $adminRole = Role::find('administrator');
        $adminRole->givePermissionTo($permissions);
    }
}
