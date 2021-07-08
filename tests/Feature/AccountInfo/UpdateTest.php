<?php

namespace Tests\Feature\AccountInfo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;
use App\Models\AccountInfo;

class UpdateTest extends TestCase
{
    public function test_controller_and_request()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([]));
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
        ];

        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('order', $data['order'])
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->etc()
                )
        );
    }

    public function test_middleware_success()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $this->json('put', $route)->assertStatus(200);
    }

    public function test_middleware_success_creator_lack_manage_game()
    {
        $accountInfo = AccountInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountInfo->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $this->json('put', $route)->assertStatus(200);
    }

    public function test_middleware_fail_lack_update_game()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $this->json('put', $route)->assertStatus(403);
    }

    public function test_middleware_fail_creator_lack_update_game()
    {
        $accountInfo = AccountInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $accountInfo->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $this->json('put', $route)->assertStatus(403);
    }
}
