<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $admin = User::firstOrCreate([
            'email' => 'dinhdjj@gmail.com',
        ], [
            'name' => 'Lê Định',
            'password' => Hash::make('12345678'),
        ]);
        $admin->assignRole('administrator');

        $tester = User::firstOrCreate([
            'email' => 'dinhdjj.test@gmail.com',
        ], [
            'name' => 'Lê Định Tester',
            'password' => Hash::make('12345678'),
        ]);
    }
}
