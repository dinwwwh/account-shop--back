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
    public function test_register()
    {
        $route = route('register');
        $data = [
            'name' => 'register test.' . Str::random(),
            'password' => '12345678',
        ];
        $data['password_confirm'] = $data['password'];
        $condition = true;
        do {
            $data['email'] = Str::random(40) . '@ledinh.com';
            $emails = User::all('email')->pluck('email');
            if (!$emails->contains($data['email'])) {
                $condition = false;
            }
        } while ($condition);

        $res = $this->json('post', $route, $data);
        $res->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }

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
        $this->json('post', route('test.auth'))->assertStatus(200);
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
}
