<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\AccountStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StartApprovingTest extends Helper
{
    public function test_controller_and_middleware_success_approver()
    {
        $config = config('account.status_codes_pending_approval', []);
        $count = 0;

        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $approver = $this->makeAuth(
            [],
            $account->accountType->approvableUsers()->inRandomOrder()->first(),
            true
        );
        $this->actingAs($approver);
        $route = route('account.start-approving', ['account' => $account]);
        $res = $this->json('post', $route);
        $res->assertStatus(204);

        $account->refresh();
        $this->assertTrue(in_array(
            $account->latestAccountStatus->code,
            config('account.status_codes_approving')
        ));

        $this->assertDatabaseHas('account_statuses', [
            'account_id' => $account->getKey(),
            'creator_id' => $approver->getKey(),
            // 'code' => $account->getKey(),
            'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_START_APPROVING,
        ]);
    }

    public function test_middleware_fail_user_valid_account()
    {
        $config = config('account.status_codes_pending_approval', []);
        $count = 0;

        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $approver = $this->makeAuth(
            [],
            $account->accountType->approvableUsers->pluck('id')->toArray(),
        );
        $this->actingAs($approver);
        $route = route('account.start-approving', ['account' => $account]);
        $res = $this->json('post', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_approver_invalid_account()
    {
        $config = config('account.status_codes_pending_approval', []);
        $count = 0;

        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $approver = $this->makeAuth(
            [],
            $account->accountType->approvableUsers()->inRandomOrder()->first(),
            true
        );
        $this->actingAs($approver);
        $route = route('account.start-approving', ['account' => $account]);
        $res = $this->json('post', $route);
        $res->assertStatus(403);
    }
}
