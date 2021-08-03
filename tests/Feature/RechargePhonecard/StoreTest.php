<?php

namespace Tests\Feature\RechargePhonecard;

use App\Helpers\ArrayHelper;
use App\Models\Setting;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        $this->actingAs($this->makeAuth());
        $route = route('recharge-phonecard.store');
        $telcos = Setting::getValidatedOrFail('recharge_phonecard_manual_telcos');
        $data = [
            'telco' => Arr::random(array_keys($telcos)),
            'serial' => Str::random(),
            'code' => Str::random(),
            'port' => config('recharge-phonecard.ports.manual'),
        ];
        $data['faceValue'] = Arr::random(array_keys($telcos[$data['telco']]));

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);

        $this->assertDatabaseHas(
            'recharge_phonecards',
            ArrayHelper::convertArrayKeysToSnakeCase($data)
        );
    }

    public function test_request_fail()
    {
        $this->actingAs($this->makeAuth());
        $route = route('recharge-phonecard.store');
        $data = [
            'telco' => 'VIETTEL',
            'serial' => Str::random(),
            'code' => Str::random(),
            // 'faceValue' => Arr::random(config('recharge-phonecard.face-values', [])),
            'port' => Arr::random(config('recharge-phonecard.ports', [])),
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(422);
    }

    public function test_middleware_fail_unauthenticated()
    {
        $route = route('recharge-phonecard.store');
        $res = $this->json('post', $route);
        $res->assertStatus(401);
    }
}
