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
}
