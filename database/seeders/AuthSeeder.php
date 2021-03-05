<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'email' => 'dinhdjj@gmail.com',
        ], [
            'name' => 'Lê Định',
            'password' => Hash::make('12345678'),
            'role_id' => 1,
        ]);

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
    }
}
