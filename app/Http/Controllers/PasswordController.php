<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response(['message' => __($status)], 204)
            : response(['error' => __($status)], 400);

        // return response([], 204);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response(['message' => __($status)], 204)
            : response(['error' => __($status)], 204);

        // return response([], 204);
    }
}
