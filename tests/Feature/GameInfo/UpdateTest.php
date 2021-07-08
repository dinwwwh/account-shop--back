<?php

namespace Tests\Feature\GameInfo;

use App\Models\GameInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;
use App\Models\Role;
use Arr;

class UpdateTest extends TestCase
{
    public function test_controller_and_request()
    {
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $this->actingAs($this->makeAuth([]));

        # Case rule's required is null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => null,
                'requiredRoleKeys' => [Role::inRandomOrder()->first()->key],
            ],
            '_requiredModelRelationships' => ['rule.requiredRoles']
        ];
        $res = $this->json('put', $route, $data);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.order', $data['order'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.rule.required', $data['rule']['required'])
                ->where('data.rule.requiredRoles.0.key', $data['rule']['requiredRoleKeys'][0])
        );

        // # Case rule's required isn't null
        $data = [
            'order' => rand(1, 5),
            'name' => Str::random(40),
            'description' => Str::random(80),
            'rule' => [
                'required' => Arr::random([true, false]),
                'requiredRoleKeys' => ['tester'],
            ],
            '_requiredModelRelationships' => ['rule.requiredRoles']
        ];
        $res = $this->json('put', $route, $data);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.order', $data['order'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.rule.required', $data['rule']['required'])
                ->where('data.rule.requiredRoles', [])
        );
    }

    public function test_middleware_success()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $this->json('put', $route)->assertStatus(200);
    }

    public function test_middleware_success_creator_lack_manage_game()
    {
        $gameInfo = GameInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $gameInfo->game->creator);
        $this->actingAs($user);
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $this->json('put', $route)->assertStatus(200);
    }

    public function test_middleware_fail_lack_update_game()
    {
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $gameInfo = GameInfo::inRandomOrder()->first();
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $this->json('put', $route)->assertStatus(403);
    }

    public function test_middleware_fail_creator_lack_update_game()
    {
        $gameInfo = GameInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game'], $gameInfo->game->creator);
        $this->actingAs($user);
        $route = route('game-info.update', ['gameInfo' => $gameInfo]);
        $this->json('put', $route)->assertStatus(403);
    }
}
