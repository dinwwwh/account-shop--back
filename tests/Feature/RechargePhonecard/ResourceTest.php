<?php

namespace Tests\Feature\RechargePhonecard;

use App\Helpers\ArrayHelper;
use App\Models\RechargePhonecard;
use App\Models\User;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Str;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    public function test_readable_sensitive_info_with_manager()
    {
        $rechargePhonecard = RechargePhonecard::inRandomOrder()->first();
        $route = route('recharge-phonecard.show', ['rechargePhonecard' => $rechargePhonecard]);

        $this->actingAs(
            $this->makeAuth(
                ['manage_recharge_phonecard'],
                [],
                true
            )
        );

        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn (AssertableJson $json) => $json
                ->where('data.code', $rechargePhonecard->code)
        );
    }

    public function test_readable_sensitive_info_with_creator()
    {
        $rechargePhonecard = RechargePhonecard::where('status', config('recharge-phonecard.statuses.approving'))
            ->inRandomOrder()->first();
        $route = route('recharge-phonecard.show', ['rechargePhonecard' => $rechargePhonecard]);

        $this->actingAs(
            $this->makeAuth(
                [],
                $rechargePhonecard->creator,
                true
            )
        );

        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn (AssertableJson $json) => $json
                ->where('data.code', $rechargePhonecard->code)
        );
    }

    public function test_readable_sensitive_info_with_approver_and_valid_recharge_phonecard()
    {
        $rechargePhonecard = RechargePhonecard::where('status', config('recharge-phonecard.statuses.approving'))
            ->inRandomOrder()->first();
        $route = route('recharge-phonecard.show', ['rechargePhonecard' => $rechargePhonecard]);

        $this->actingAs(
            $this->makeAuth(
                ['approve_recharge_phonecard'],
                $rechargePhonecard->approver,
                true
            )
        );

        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn (AssertableJson $json) => $json
                ->where('data.code', $rechargePhonecard->code)
        );
    }

    public function test_unreadable_sensitive_info_with_approver_and_invalid_recharge_phonecard()
    {
        $rechargePhonecard = RechargePhonecard::where('approver_id', '!=', null)
            ->inRandomOrder()->first();
        $rechargePhonecard->update([
            'status' => Arr::random([
                config('recharge-phonecard.statuses.error'),
                config('recharge-phonecard.statuses.success'),
            ])
        ]);
        $route = route('recharge-phonecard.show', ['rechargePhonecard' => $rechargePhonecard]);

        $this->actingAs(
            $this->makeAuth(
                ['manage_recharge_phonecard'],
                $rechargePhonecard->approver
            )
        );

        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn (AssertableJson $json) => $json
                ->has(
                    'data',
                    fn (AssertableJson $json) => $json
                        ->missing('code')
                        ->etc()
                )
        );
    }
}
