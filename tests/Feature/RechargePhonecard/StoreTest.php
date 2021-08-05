<?php

namespace Tests\Feature\RechargePhonecard;

use App\Helpers\ArrayHelper;
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
        $telcos = config('recharge-phonecard.manual_telcos', []);

        $randomTelco = Arr::random($telcos);
        $telco = $randomTelco['key'];
        $faceValue = Arr::random(array_map(fn ($fv) => $fv['value'], $randomTelco['faceValues']));
        $data = [
            'telco' => $telco,
            'serial' => Str::random(),
            'code' => Str::random(),
            'port' => config('recharge-phonecard.ports.manual'),
            'faceValue' => $faceValue
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);

        $this->assertDatabaseHas(
            'recharge_phonecards',
            ArrayHelper::convertArrayKeysToSnakeCase($data, 1)
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
