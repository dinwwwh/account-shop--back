<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApproveTest extends TestCase
{
    public function test_controller_and_request()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 0)
            ->where('status_code', '<=', 99)
            ->first();
        $route = route('account.approve', ['account' => $account]);
        $this->actingAs($this->makeAuth([]));

        $res = $this->json('post', $route);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.statusCode', 480)
        );
    }
}
