<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    public function test_case_has_sensitive_info_1()
    {
        $account = Account::where('buyer_id', '!=', null)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $data = [
            '_requiredModelRelationships' => ['accountActions', 'accountInfos'],
        ];
        $user = $this->makeAuth([]);
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );
    }

    public function test_case_has_sensitive_info_2()
    {
        $data = [
            '_requiredModelRelationships' => ['accountActions', 'accountInfos'],
        ];
        $account = Account::where('buyer_id', '!=', null)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $buyer = $this->makeAuth(['manage_account'], $account->buyer);
        $res = $this->actingAs($buyer)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );
    }

    public function a()
    {
        $account = Account::where('buyer_id', '!=', null)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $buyer = $account->buyer;
        $buyer->syncPermissions();
        $buyer->syncRoles();
        $buyer->refresh();
        $user = User::whereNotIn('id', [$account->buyer->getKey(), $account->creator->getKey()])
            ->inRandomOrder()
            ->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();
        $data = [
            '_requiredModelRelationships' => ['accountActions', 'accountInfos'],
        ];

        # case as a manager
        $user->givePermissionTo('manage_account');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case as a buyer
        $res = $this->actingAs($buyer)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case as a creator
        $account = Account::whereIn('status_code', [0, 440])
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $creator = $account->creator;
        $creator->syncRoles();
        $creator->syncPermissions();
        $creator->refresh();
        $res = $this->actingAs($creator)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case user can approve account
        $account = Account::where('status_code', '<=', 99)
            ->inRandomOrder()
            ->first();
        $route = route('account.show', ['account' => $account]);
        $user->revokePermissionTo('manage_account');
        $user->givePermissionTo('approve_account');
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data.accountActions')
                ->has('data.accountInfos')
                ->where('data.password', $account->password)
        );

        # case other user
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();
        $res = $this->actingAs($user)
            ->json('get', $route, $data);
        $res->assertJson(
            fn ($json) => $json
                ->has('data')
                ->missing('data.accountActions')
                ->missing('data.accountInfos')
                ->missing('data.password')
        );
    }
}
