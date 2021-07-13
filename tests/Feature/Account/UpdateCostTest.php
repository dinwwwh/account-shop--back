<?php

namespace Tests\Feature\Account;

use App\Models\Account;

class UpdateCostTest extends Helper
{
    public function test_controller()
    {
        $this->actingAs($this->makeAuth([]));
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-cost', ['account' => $account]);
        $data = [
            'cost' => rand(0, 999999)
        ];

        $res = $this->json('patch', $route, $data);
        $res->assertStatus(204);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->getKey(),
            'cost' => $data['cost'],
        ]);
    }

    public function test_middleware_success_manager()
    {
        $account = Account::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([], [
            $account->creator->getKey(),
            $account->latestAccountStatus->creator->getKey(),
        ]));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_success_creator()
    {
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (!in_array(
            $account->latestAccountStatus->code,
            config('account.creator.updatable_cost_status_codes', []),
        ));

        $this->actingAs($this->makeAuth([], $account->creator, true));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_success_approver()
    {
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_cost_status_codes', []),
            )
            ||  $account->latestAccountStatus->creator_id == $account->creator_id
        );

        $approver = $account->latestAccountStatus->creator;
        $account->accountType->approvableUsers()->attach([$approver->getKey() => ['status_code' => 480]]);
        $this->actingAs($this->makeAuth(
            [],
            $approver,
            true
        ));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_fail_manager_lack_update_game()
    {
        $account = Account::inRandomOrder()->first();
        $this->actingAs($this->makeAuth([
            'update_game'
        ], [
            $account->creator->getKey(),
            $account->latestAccountStatus->creator->getKey(),
        ]));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_creator()
    {
        $count =  0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (in_array(
            $account->latestAccountStatus->code,
            config('account.creator.updatable_cost_status_codes', []),
        ));

        $this->actingAs($this->makeAuth([], $account->creator, true));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_approver()
    {
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $approver = $account->accountType->approvableUsers()->where('user_id', '!=', $account->creator_id)->first();
            $count++;
            if ($count == 100) return;
        } while (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_cost_status_codes', []),
            )
            || is_null($approver)
        );

        $this->actingAs($this->makeAuth(
            [],
            $approver,
            true
        ));
        $route = route('account.update-cost', ['account' => $account]);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }
}
