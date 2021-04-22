<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Game;
use App\Models\user;
use App\Models\AccountType;
use App\Models\Role;
use Database\Seeders\ForTestSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ManageAccountFeeInAccountTest extends TestCase
{
    public function testCalculateFee()
    {
        $user = User::inRandomOrder()->first();
        $this->actingAs($user);
        $game = Game::inRandomOrder()->first();

        $account = Account::inRandomOrder()->first();

        $fee1 = $account->calculateFee();

        $fee2 = 0;
        foreach ($account->accountType->accountFees as $accountFee) {
            if (
                is_null($accountFee->maximum_cost) || $account->cost <= $accountFee->maximum_cost
                && is_null($accountFee->minimum_cost) || $account->cost >= $accountFee->minimum_fee
            ) {
                $temporaryFee = $account->cost * $accountFee->percentage_cost / 100;
                $temporaryFee += $accountFee->direct_fee;
                $temporaryFee = !is_null($accountFee->maximum_fee) && $accountFee->maximum_fee < $temporaryFee
                    ? $accountFee->maximum_fee
                    : $temporaryFee;
                $temporaryFee = !is_null($accountFee->minimum_fee) && $accountFee->minimum_fee > $temporaryFee
                    ? $accountFee->minimum_fee
                    : $temporaryFee;
                $fee2 += $temporaryFee;
            }
        }

        $this->assertTrue($fee1 === $fee2);
    }

    public function testCarefully()
    {
        for ($i = 0; $i < 999; $i++) {
            $this->testCalculateFee();
        }
    }
}
