<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\user;

class A0RunFirstTest extends TestCase
{
    public function testMigrateDatabaseForTest()
    {
        $user = User::first();
        $this->actingAs($user);
        $this->seed(\Database\Seeders\ForTestSeeder::class);

        $this->assertTrue(true);
    }
}
