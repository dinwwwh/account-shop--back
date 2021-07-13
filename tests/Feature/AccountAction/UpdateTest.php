<?php

namespace Tests\Feature\AccountAction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AccountAction;
use Str;
use Arr;
use Illuminate\Testing\Fluent\AssertableJson;

class UpdateTest extends TestCase
{
    public function test_controller()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $creator = $this->makeAuth([], $accountAction->creator);
        $this->actingAs($creator);
        $route = route('account-action.update', ['accountAction' => $accountAction]);

        # Case required is null
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
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
            fn (AssertableJson $j) => $j
                ->where('data.rule.datatype', 'boolean')
                ->where('data.rule.required', false)
        );
        $ruleId = $res->getData()->data->rule->id;

        $this->assertDatabaseHas('account_actions', [
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
            'video_path' => $data['videoPath'],
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

    /**
     * Case is manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountAction = AccountAction::inRandomOrder()->first();
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountAction = AccountAction::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountAction->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(200);
    }

    /**
     * Case is creator of game but lack update_game manage_game permission
     */
    public function test_middleware_fail()
    {
        $accountAction = AccountAction::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game', 'manage_game'], $accountAction->accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.update', ['accountAction' => $accountAction]);
        $this->json('put', $route)->assertStatus(403);
    }
}
