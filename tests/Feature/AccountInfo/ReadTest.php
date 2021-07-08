<?php

namespace Tests\Feature\AccountInfo;

use App\Models\AccountInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReadTest extends TestCase
{
    public function test_controller_and_request()
    {
        $accountInfo = AccountInfo::inRandomOrder()->first();
        $route = route('account-info.show', ['accountInfo' => $accountInfo]);
        $res = $this->json('get', $route);
        $res->assertStatus(200);
    }
}
