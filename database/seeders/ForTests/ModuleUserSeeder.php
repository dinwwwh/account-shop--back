<?php

namespace Database\Seeders\ForTests;

use App\Models\User;
use Illuminate\Database\Seeder;

class ModuleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory()->count(10)->create();
    }
}
