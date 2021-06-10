<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterAuthRequest;
use App\Http\Requests\LoginAuthRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    /**
     * Register and store user to database
     *
     * @param \App\Http\Requests\LoginAuthRequest $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginAuthRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials, $request->remember)) {
            return new UserResource(auth()->user());
        }

        return response([
            'message' => 'Login failed.',
        ], 401);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response([
            'message' => 'Logout success!!'
        ]);
    }

    /**
     * Display the auth profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return new UserResource(auth()->user());
    }
}
