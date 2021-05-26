<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use App\Models\User;

class AuthTest extends TestCase
{
    public function test_login_and_logout()
    {
        $loginRoute = route('login');
        $logoutRoute = route('logout');
        $user = User::inRandomOrder()->first();

        // Login success
        $res = $this->json('post', $loginRoute, [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $res->assertStatus(200);
        $this->json('post', route('test.auth'))->assertStatus(403);
        // Logout success
        $this->json('post', $logoutRoute)->assertStatus(200);
        $this->json('post', route('test.auth'))->assertStatus(401);

        // Login failed
        $res = $this->json('post', $loginRoute, [
            'email' => $user->email,
            'password' => 'error_password',
        ]);
        $res->assertStatus(401);
        $this->json('post', route('test.auth'))->assertStatus(401);
        // Logout failed
        $this->json('post', $logoutRoute)->assertStatus(401);
    }

    public function test_profile()
    {
        $profileRoute = route('auth.profile');
        $user = User::inRandomOrder()->first();

        $res = $this->actingAs($user)
            ->json('get', $profileRoute);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) =>
            $json->where('data.id', $user->getKey())
        );
    }
}
