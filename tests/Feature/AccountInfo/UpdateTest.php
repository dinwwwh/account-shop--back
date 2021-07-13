<?php

namespace Tests\Feature\AccountInfo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;
use App\Models\AccountInfo;

class UpdateTest extends TestCase
{
    public function test_controller()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([]));
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
            'rule' => [
                'required' => false,
                'requiredUserIds' => [1, 2, 3],
                'unrequiredUserIds' => [4, 5, 6, 7],
            ],
            '_requiredModelRelationships' => ['rule']
        ];

        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJsonCount(3, 'data.rule.requiredUsers');
        $res->assertJsonCount(0, 'data.rule.unrequiredUsers');
        $res->assertJson(
            fn ($j) => $j
                ->where('data.rule.required', false)
        );
        $ruleId = $res->getData()->data->rule->id;

        $this->assertDatabaseHas('account_infos', [
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        foreach ($data['rule']['requiredUserIds'] as $userId) {
            $this->assertDatabaseHas('rule_user_required', [
                'user_id' => $userId,
                'rule_id' => $ruleId,
            ]);
        }

        foreach ($data['rule']['unrequiredUserIds'] as $userId) {
            $this->assertDatabaseMissing('rule_user_unrequired', [
                'user_id' => $userId,
                'rule_id' => $ruleId,
            ]);
        }
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

    public function test_middleware_fail_creator_lack_update_game_manage_game()
    {
        $accountInfo = AccountInfo::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game', 'manage_game'], $accountInfo->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.update', ['accountInfo' => $accountInfo]);
        $this->json('put', $route)->assertStatus(403);
    }
}
