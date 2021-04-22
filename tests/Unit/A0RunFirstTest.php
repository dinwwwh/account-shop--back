<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\user;
use App\Models\DiscountCode;
use Database\Seeders\ForTestSeeder;

class A0RunFirstTest extends TestCase
{
    public function testMigrateDatabaseForTest()
    {
        $user = User::inRandomOrder()->first();
        $this->actingAs($user);
        $this->seed(ForTestSeeder::class);

        $this->assertTrue(true);
    }
}
