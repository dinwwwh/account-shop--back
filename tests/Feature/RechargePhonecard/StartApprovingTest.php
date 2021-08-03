<?php

namespace Tests\Feature\RechargePhonecard;

use App\Helpers\ArrayHelper;
use App\Models\RechargePhonecard;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Str;
use Tests\TestCase;

class StartApprovingTest extends TestCase
{
    public function test_controller()
    {
        $this->actingAs($this->makeAuth());
        $rechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.pending'))
            ->first();

        $route = route('recharge-phonecard.start-approving', ['rechargePhonecard' => $rechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn (AssertableJson $json) => $json
                ->where('data.code', $rechargePhonecard->code)
        );

        $this->assertDatabaseHas(
            'recharge_phonecards',
            [
                'id' => $rechargePhonecard->getKey(),
                'status' => config('recharge-phonecard.statuses.approving'),
            ]
        );
    }

    public function test_middleware_fail_invalid_recharge_phonecard()
    {
        $this->actingAs($this->makeAuth());
        $invalidRechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', '!=', config('recharge-phonecard.statuses.pending'))
            ->first();

        $route = route('recharge-phonecard.start-approving', ['rechargePhonecard' => $invalidRechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_missing_approve_recharge_phonecard_permission()
    {
        $this->actingAs($this->makeAuth(['approve_recharge_phonecard']));
        $validRechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.pending'))
            ->first();

        $route = route('recharge-phonecard.start-approving', ['rechargePhonecard' => $validRechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }
}
