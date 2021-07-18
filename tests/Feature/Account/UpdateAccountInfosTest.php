<?php

namespace Tests\Feature\Account;

use App\Models\Account;

class UpdateAccountInfosTest extends Helper
{
    public function test_controller()
    {
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $data = [
            'rawAccountInfos' => $this->makeDataForAccountInfos($account->accountType, $user),
        ];

        $res = $this->json('patch', $route, $data);
        $res->assertStatus(204);

        foreach ($data['rawAccountInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_account_info', [
                'account_info_id' => (int)trim($key, 'id '),
                'values' => json_encode($value['values']),
            ]);
        }
    }

    public function test_middleware_success_manager()
    {
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_success_creator()
    {
        $config = config('account.creator.updatable_account_infos_status_codes', []);
        if (empty($config)) {
            $this->assertTrue(true);
            return;
        }
        do {
            $account = Account::inRandomOrder()->first();
        } while (!in_array(
            $account->latestAccountStatus->code,
            $config
        ));

        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth([], $account->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_success_approver()
    {
        $config = config('account.approver.updatable_account_infos_status_codes', []);
        if (empty($config)) {
            $this->assertTrue(true);
            return;
        }
        do {
            $account = Account::inRandomOrder()->first();
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth([], $account->latestAccountStatus->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(422);
    }

    public function test_middleware_fail_creator()
    {
        $config = config('account.creator.updatable_account_infos_status_codes', []);
        if (empty($config)) {
            $this->assertTrue(true);
            return;
        }
        do {
            $account = Account::inRandomOrder()->first();
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth([], $account->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_approver()
    {
        $count = 0;
        $config = config('account.approver.updatable_account_infos_status_codes', []);
        do {
            $account = Account::inRandomOrder()->first();
            $approver = $account->accountType->approvableUsers()->where('user_id', '!=', $account->creator_id)->first();
            $count++;
            if ($count == 100) return;
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
            || is_null($approver)
        );

        $route = route('account.update-account-infos', ['account' => $account]);
        $user = $this->makeAuth(
            [],
            $approver,
            true
        );
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }
}
