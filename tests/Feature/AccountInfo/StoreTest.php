<?php

namespace Tests\Feature\AccountInfo;

use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Str;

class StoreTest extends TestCase
{
    public function test_controller()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->actingAs($this->makeAuth([]));

        # Case normal rule
        $data = [
            'order' => rand(1, 100),
            'name' => Str::random(10),
            'description' => Str::random(30),
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
                ->where('data.rule.required', true)
        );
        $ruleId = $res->getData()->data->rule->id;

        $this->assertDatabaseHas('account_infos', [
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
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
     * Case is manager of game
     */
    public function test_middleware_success_1()
    {
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator of game but lack manage_game
     */
    public function test_middleware_success_2()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(422);
    }

    /**
     * Case is creator of game but lack update_game manage_game permission
     */
    public function test_middleware_fail()
    {
        $accountType = AccountType::where('creator_id', '!=', null)
            ->inRandomOrder()->first();
        $user = $this->makeAuth(['update_game', 'manage_game'], $accountType->game->creator);
        $this->actingAs($user);
        $route = route('account-info.store', ['accountType' => $accountType]);
        $this->json('post', $route)->assertStatus(403);
    }
}
