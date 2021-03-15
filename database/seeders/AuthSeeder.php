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
    }
}
