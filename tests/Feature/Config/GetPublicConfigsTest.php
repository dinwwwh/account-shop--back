<?php

namespace Tests\Feature\Config;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetPublicConfigsTest extends TestCase
{
    public function test_controller()
    {
        $route = route('config.get-public-configs');

        $res = $this->json('get', $route);
        $res->assertStatus(200);

        foreach ($res->getData()->data as $setting) {
            $this->assertTrue($setting->public);
        }
    }
}
