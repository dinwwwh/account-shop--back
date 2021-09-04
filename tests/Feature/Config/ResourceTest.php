<?php

namespace Tests\Feature\Config;

use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     */
    public function test_as_user_can_read_data()
    {
        $this->actingAs($this->makeAuth(['view_any_config'], [], true));
        $config = Config::factory()->state(['public' => 0])->create();

        $res = $this->json('get', route('config.show', compact('config')));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('data', $config->data)
                ->etc()
        );
    }

    public function test_as_user_can_not_read_data()
    {
        $this->actingAs($this->makeAuth(['view_any_config'], []));
        $config = Config::factory()->state(['public' => 0])->create();

        $res = $this->json('get', route('config.show', compact('config')));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('data', null)
                ->etc()
        );
    }
}
