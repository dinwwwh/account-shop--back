<?php

namespace Tests\Feature\AccountType;

use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReadTest extends TestCase
{
    public function test_controller()
    {
        // Initial data
        $accountType = AccountType::inRandomOrder()->first();

        /**
         * Have power
         * -----------------------------
         */
        $res = $this->json('get', route('account-type.show', ['accountType' => $accountType]));
        $res->assertStatus(200);
    }

    public function test_get_calculated_fee()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $data = [
            'cost' => rand(0, 999999),
        ];
        $fee = $accountType->calculateFee($data['cost']);
        $res = $this
            ->json('get', route('account-type.calculate-fee', ['accountType' => $accountType]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('data.result', $fee)
        );
    }
}
