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

        $administrator = Role::firstOrCreate(
            [
                'id' => 1
            ],
            [
                'name' => 'Admin',
                'description' => 'Người này có thể làm tất cả',
                'style_classes' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-green-800'
            ]
        );

        Role::firstOrCreate(
            [
                'id' => 2
            ],
            [
                'name' => 'Dân thường',
                'description' => 'người dùng bình thường, có những quyền cơ bản.',
                'style_classes' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-800'
            ]
        );


        $modules = [
            'user' => 'người dùng',
            'role' => 'vai trò',
            'permission' => 'quyền hạn',
            'account' => 'tài khoản game',
            'game' => 'game',
            'game_info' => 'thông tin game',
            'account_type' => 'kiểu tài khoản',
            'account_info' => 'thông tin tài khoản',
            'account_action' => 'hành động thực hiện trên tài khoản',
        ];

        $permissionKeys = [];
        // Create permissions and sync it with roles
        foreach ($modules as $key => $name) {
            // This help sort permission to easy
            $parent = Permission::firstOrCreate(
                [
                    'key' => 'module_' . $key,
                ],
                [
                    'name' => 'module ' . $name,
                ],
            );

            $permissionKeys[] = Permission::firstOrCreate(
                [
                    'key' => 'view_any_' . $key,
                ],
                [
                    'name' => 'xem bất kỳ ' . $name,
                    'description' => 'Xác định liệu người dùng có thể xem thông xin của tất cả các bản ghi ' . $name . '.',
                    'parent_key' => $parent->key,
                ],
            )->key;

            $permissionKeys[] = Permission::firstOrCreate(
                [
                    'key' => 'create_' . $key,
                ],
                [
                    'name' => 'tạo ' . $name,
                    'description' => 'Xác định liệu người dùng có thể thêm mới một ' . $name . '.',
                    'parent_key' => $parent->key,
                ],
            )->key;

            $permissionKeys[] = Permission::firstOrCreate(
                [
                    'key' => 'update_' . $key,
                ],
                [
                    'name' => 'cập nhật ' . $name,
                    'description' => 'Xác định liệu người dùng có thể cập nhật bản ghi của ' . $name . ' do chính họ tạo ra.',
                    'parent_key' => $parent->key,
                ],
            )->key;

            $permissionKeys[] = Permission::firstOrCreate(
                [
                    'key' => 'delete_' . $key,
                ],
                [
                    'name' => 'xoá ' . $name,
                    'description' => 'Xác định liệu người dùng có thể xoá một ' . $name . ' do chính họ tạo ra.',
                    'parent_key' => $parent->key,
                ],
            )->key;

            // $permissionKeys[] = Permission::firstOrCreate(
            //     [
            //         'key' => 'advance_' . $key,
            //     ],
            //     [
            //         'name' => 'quền nâng cao - ' . $name,
            //         'description' => 'Xác định người dùng có thể làm một số thứ nâng cao liên quan đên '
            //             . $name . ', thường chỉ một số người đặc biệt như ADM, manager... mới nên có quyền này.',
            //         'parent_key' => $parent->key,
            //     ],
            // )->key;

            if ($key == 'user') {
                $a = 0;
            }
        }

        $administrator->permissions()->sync($permissionKeys);
    }
}
