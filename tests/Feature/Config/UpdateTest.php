<?php

namespace Tests\Feature\Config;

use App\Models\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function test_controller()
    {
        $config = Config::factory()->create();
        $data = [
            'data' => '123',
            'description' => 'description',
        ];

        $route = route('config.update', compact('config'));

        $this->actingAs($this->makeAuth());
        $this->json('put', $route, $data)
            ->assertStatus(200);

        $this->assertDatabaseHas('configs', [
            'key' => $config->getKey(),
            'data' => json_encode($data['data']),
            'description' => $data['description'],
        ]);
    }

    public function test_request_fails_case_expect_boolean()
    {
        $config = Config::factory()
            ->state([
                'rules_of_data' => ['boolean']
            ])
            ->create();
        $data = [
            'data' => '123',
            'description' => 'description',
        ];

        $route = route('config.update', compact('config'));

        $this->actingAs($this->makeAuth());
        $this->json('put', $route, $data)
            ->assertStatus(422);
    }

    public function test_request_fails_case_expect_array()
    {
        $config = Config::factory()
            ->state([
                'rules_of_data' => ['array']
            ])
            ->create();
        $data = [
            'data' => '123',
            'description' => 'description',
        ];

        $route = route('config.update', compact('config'));

        $this->actingAs($this->makeAuth());
        $this->json('put', $route, $data)
            ->assertStatus(422);
    }

    public function test_middleware_success_case_has_update_config_permissions()
    {
        $config = Config::factory()->create();
        $route = route('config.update', compact('config'));
        $user = $this->makeAuth(['update_config'], [], true);
        $this->actingAs($user);

        $this->json('put', $route)
            ->assertStatus(200);
    }

    public function test_middleware_fail_case_lack_update_config_permissions()
    {
        $config = Config::factory()->create();
        $route = route('config.update', compact('config'));
        $this->actingAs($this->makeAuth(['update_config', []]));
        $this->json('put', $route)
            ->assertStatus(403);
    }
}
