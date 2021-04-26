<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Seeder::call(PermissionSeeder::class);
        Seeder::call(RoleSeeder::class);
        Seeder::call(AuthSeeder::class);
        // Seeder::call(ForTestSeeder::class); Never call it, if you don't know what you do
    }
}
