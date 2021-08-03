<?php

namespace Tests\Feature\RechargePhonecard;

use App\Helpers\ArrayHelper;
use App\Models\RechargePhonecard;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EndApprovingTest extends TestCase
{
    public function test_controller_with_success_is_true()
    {
        $telcos = Setting::getValidatedOrFail('recharge_phonecard_manual_telcos');
        $rechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.approving'))
            ->first();
        $this->actingAs($this->makeAuth());

        $oldGoldCoin = $rechargePhonecard->creator->gold_coin;
        $newGoldCoin =
            $oldGoldCoin
            + (int)($rechargePhonecard->face_value * $telcos[$rechargePhonecard->telco][$rechargePhonecard->face_value] / 100);

        $route = route('recharge-phonecard.end-approving', ['rechargePhonecard' => $rechargePhonecard]);
        $res = $this->json('patch', $route, [
            'success' => true,
        ]);
        $res->assertStatus(204);

        $this->assertDatabaseHas(
            'recharge_phonecards',
            [
                'id' => $rechargePhonecard->getKey(),
                'status' => config('recharge-phonecard.statuses.success'),
            ]
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $rechargePhonecard->creator->getKey(),
                'gold_coin' => $newGoldCoin,
            ]
        );
    }

    public function test_controller_with_success_is_false()
    {
        $rechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.approving'))
            ->first();
        $this->actingAs($this->makeAuth());

        $oldGoldCoin = $rechargePhonecard->creator->gold_coin;

        $route = route('recharge-phonecard.end-approving', ['rechargePhonecard' => $rechargePhonecard]);
        $res = $this->json('patch', $route, [
            'success' => false,
        ]);
        $res->assertStatus(204);

        $this->assertDatabaseHas(
            'recharge_phonecards',
            [
                'id' => $rechargePhonecard->getKey(),
                'status' => config('recharge-phonecard.statuses.error'),
            ]
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $rechargePhonecard->creator->getKey(),
                'gold_coin' => $oldGoldCoin,
            ]
        );
    }

    public function test_middleware_fail_invalid_recharge_phonecard()
    {
        $this->actingAs($this->makeAuth());
        $invalidRechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', '!=', config('recharge-phonecard.statuses.approving'))
            ->first();

        $route = route('recharge-phonecard.end-approving', ['rechargePhonecard' => $invalidRechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_valid_recharge_phonecard_but_invalid_auth()
    {
        $validRechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.approving'))
            ->first();
        $this->actingAs(
            $this->makeAuth(
                ['manage_recharge_phonecard'],
                User::where('id', '!=', $validRechargePhonecard->approver_id)->first()
            )
        );


        $route = route('recharge-phonecard.end-approving', ['rechargePhonecard' => $validRechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_success_valid_recharge_phonecard_and_has_approve_recharge_phonecard_permission()
    {
        $validRechargePhonecard = RechargePhonecard::inRandomOrder()
            ->where('status', config('recharge-phonecard.statuses.approving'))
            ->first();
        $this->actingAs(
            $this->makeAuth(
                ['approve_recharge_phonecard'],
                $validRechargePhonecard->approver,
                true
            )
        );


        $route = route('recharge-phonecard.end-approving', ['rechargePhonecard' => $validRechargePhonecard]);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }
}
