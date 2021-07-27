<?php

namespace Tests\Feature\AccountAction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountType;
use Str;
use Arr;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);

        # Case required is true
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(10),
            'videoPath' => Str::random(10),
            'rule' => [
                'required' => true,
                'rawRequiredUsers' => [1 => [], 2 => [], 3 => []],
                'rawUnrequiredUsers' => [4 => [], 5 => [], 6 => [], 7 => []],
            ],
            '_requiredModelRelationships' => ['rule']
        ];

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJsonCount(0, 'data.rule.requiredUsers');
        $res->assertJsonCount(4, 'data.rule.unrequiredUsers');
        $res->assertJson(
            fn ($j) => $j
                ->where('data.rule.datatype', 'boolean')
                ->where('data.rule.allowableValues', [true])
                ->where('data.rule.required', true)
        );
        $ruleId = $res->getData()->data->rule->id;

        $this->assertDatabaseHas('account_actions', [
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
            'video_path' => $data['videoPath'],
        ]);

        foreach ($data['rule']['rawRequiredUsers'] as $id => $pivot) {
            $this->assertDatabaseMissing('rule_user_required', [
                'user_id' => $id,
                'rule_id' => $ruleId,
            ]);
        }

        foreach ($data['rule']['rawUnrequiredUsers'] as $id => $pivot) {
            $this->assertDatabaseHas('rule_user_unrequired', [
                'user_id' => $id,
                'rule_id' => $ruleId,
            ]);
        }
    }

    /**
     * Case if manager
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator of game but lack update_game manage_game permission
     */
    public function test_middleware_fail_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game', 'manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-action.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }
}
