<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ForTestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Artisan::call('migrate:fresh --seed');
        Seeder::call(ForTests\ModuleUserSeeder::class);
        Seeder::call(ForTests\ModuleGameSeeder::class);
        Seeder::call(ForTests\ModuleDiscountCodeSeeder::class);
    }
}
