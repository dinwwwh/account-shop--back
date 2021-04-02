<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->makeBasePermissions();
        $this->makeGamePermissions();
    }


    /**
     * Create some base permissions such as:
     * view any, manage, create, update, delete
     *
     * @return void
     */
    public function makeBasePermissions()
    {
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

            Permission::firstOrCreate(
                [
                    'key' => 'view_any_' . $key,
                ],
                [
                    'name' => 'xem bất kỳ ' . $name,
                    'description' => 'Xác định liệu người dùng có thể xem thông xin cơ bản của tất cả các bản ghi ' . $name . '.',
                    'parent_key' => $parent->key,
                ],
            );

            Permission::firstOrCreate(
                [
                    'key' => 'manage_' . $key,
                ],
                [
                    'name' => 'quản lý ' . $name,
                    'description' => 'Xác định liệu người dùng có thể quản lý ' . $name . ', bao gồm thêm một số quyền nâng cao khác.',
                    'parent_key' => $parent->key,
                ],
            );

            Permission::firstOrCreate(
                [
                    'key' => 'create_' . $key,
                ],
                [
                    'name' => 'tạo ' . $name,
                    'description' => 'Xác định liệu người dùng có thể thêm mới một ' . $name . '.',
                    'parent_key' => $parent->key,
                ],
            );

            Permission::firstOrCreate(
                [
                    'key' => 'update_' . $key,
                ],
                [
                    'name' => 'cập nhật ' . $name,
                    'description' => 'Xác định liệu người dùng có thể cập nhật bản ghi của ' . $name . ' do chính họ tạo ra.',
                    'parent_key' => $parent->key,
                ],
            );

            Permission::firstOrCreate(
                [
                    'key' => 'delete_' . $key,
                ],
                [
                    'name' => 'xoá ' . $name,
                    'description' => 'Xác định liệu người dùng có thể xoá một ' . $name . ' do chính họ tạo ra.',
                    'parent_key' => $parent->key,
                ],
            );
        }
    }

    /**
     * Create internal game permission
     *
     * @return void
     */
    public function makeGamePermissions()
    {
    }
}
