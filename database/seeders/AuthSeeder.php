<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::create([
            'email' => 'dinhdjj@gmail.com',
            'name' => 'Le Dinh',
            'password' => Hash::make('12345678'),
        ]);
    }
}
