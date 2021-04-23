<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class ManageCoinInUserTest extends TestCase
{
    public function test_checkEnoughGoldCoin()
    {
        $user = User::inRandomOrder()->first();

        # $coin == $user->gold_coin
        $coin = $user->gold_coin;
        $this->assertEquals(true, $user->checkEnoughGoldCoin($coin));

        # $coin > $user->gold_coin
        $coin = $user->gold_coin + rand(1, 9999);
        $this->assertEquals(false, $user->checkEnoughGoldCoin($coin));

        # $coin < $user->gold_coin
        $coin = $user->gold_coin - rand(1, 9999);
        $this->assertEquals(true, $user->checkEnoughGoldCoin($coin));
    }

    public function test_reduceGoldCoin()
    {
        $user = User::inRandomOrder()->first();

        # $coin == $user->gold_coin
        $iniCoin = $user->gold_coin;
        $coin = $user->gold_coin;
        $this->assertEquals(true, $user->reduceGoldCoin($coin));
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'gold_coin' => $iniCoin - $coin,
        ]);

        # $coin > $user->gold_coin
        $iniCoin = $user->gold_coin;
        $coin = $user->gold_coin + rand(1, 9999);
        $this->assertEquals(false, $user->reduceGoldCoin($coin));
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'gold_coin' => $iniCoin,
        ]);

        # $coin < $user->gold_coin
        $iniCoin = $user->gold_coin;
        $coin = $user->gold_coin - rand(1, 9999);
        $this->assertEquals(true, $user->reduceGoldCoin($coin));
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'gold_coin' => $iniCoin - $coin,
        ]);
    }

    public function test_carefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->test_checkEnoughGoldCoin();
            $this->test_reduceGoldCoin();
        }

        // fresh database for testing env
        $user = User::inRandomOrder()->first();
        $this->actingAs($user);
        $this->seed(\Database\Seeders\ForTestSeeder::class);
    }
}
