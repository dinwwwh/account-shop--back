<?php

namespace Database\Seeders\ForTests;

use App\Models\DiscountCode;
use Illuminate\Database\Seeder;

class ModuleDiscountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $discountCodes = DiscountCode::factory()->count(100)->create();
    }
}
