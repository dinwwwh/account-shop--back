<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_register()
    {
        $route = route('register');
        $data = [
            'name' => 'register test.' . Str::random(),
            'password' => '12345678',
            'password_confirmation' => '12345678',
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
}
