<?php

namespace Database\Seeders\ForTests;

use App\Models\RechargePhonecard;
use App\Models\User;
use Auth;
use Illuminate\Database\Seeder;

class ModuleRecharge extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        foreach ($users as $user) {
            Auth::logout();
            Auth::setUser($user);
            RechargePhonecard::factory()->count(5)->create();
        }
    }
}
