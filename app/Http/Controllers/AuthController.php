<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterAuthRequest;
use App\Http\Requests\LoginAuthRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register and store user to database
     *
     * @param App\Http\Requests\RegisterAuthRequest $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterAuthRequest $request)
    {

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        try {
            DB::beginTransaction();

            $user = User::create($data)->refresh();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return new UserResource($user);
    }

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
            $request->session()->regenerate();

            return response([
                'message' => 'Login success!!'
            ]);
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
}
