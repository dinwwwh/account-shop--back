<?php

namespace Tests\Feature\AccountAction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AccountAction;

class ReadTest extends TestCase
{
    public function test_controller()
    {
        $accountAction = AccountAction::inRandomOrder()->first();
        $route = route('account-action.show', ['accountAction' => $accountAction]);
        $res = $this->json('get', $route);
        $res->assertStatus(200);
    }
}
