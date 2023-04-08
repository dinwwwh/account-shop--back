<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ForDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Seeder::call(ForTests\ModuleUserSeeder::class);
        Seeder::call(ForTests\ModuleGameSeeder::class);
        Seeder::call(ForTests\ModuleRecharge::class);
        Seeder::call(ForTests\CouponSeeder::class);
    }
}
