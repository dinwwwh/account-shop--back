<?php

namespace Database\Seeders\ForTests;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coupons = Coupon::factory()->count(100)->create();
    }
}
