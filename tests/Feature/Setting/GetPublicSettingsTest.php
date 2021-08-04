<?php

namespace Tests\Feature\Setting;

use App\Helpers\ArrayHelper;
use App\Models\Setting;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Str;
use Tests\TestCase;

class GetPublicSettingsTest extends TestCase
{
    public function test_controller()
    {
        $route = route('setting.get-public-settings');
        $expectedSettings = Setting::where('public', true)->get();


        $res = $this->json('get', $route);
        $res->assertStatus(200);

        foreach ($res->getData()->data as $setting) {
            $this->assertTrue($expectedSettings->contains($setting->key));
            $this->assertTrue($setting->public);
        }

        foreach ($expectedSettings as $expectedSetting) {
            $fail = true;
            foreach ($res->getData()->data as $setting) {
                if ($setting->key === $expectedSetting->getKey()) $fail = false;
            }
            $this->assertTrue(!$fail);
        }
    }
}
