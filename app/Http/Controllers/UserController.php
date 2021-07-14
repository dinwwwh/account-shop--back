<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Register and store user to database
     *
     * @param \App\Http\Requests\RegisterUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterUserRequest $request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        $user = User::create($data)->refresh();

        event(new Registered($user));

        return new UserResource($user);
    }

    /**
     * Verify user email
     *
     * @param \Illuminate\Foundation\Auth\EmailVerificationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response([
            'message' => 'Xác thực email thành công.'
        ]);
    }

    /**
     * Send email verification for user again
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendEmailVerificationNotification(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response([
            'message' => 'Email xác thực đã được gửi.'
        ]);
    }

    /**
     * Find users by keyword
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        $users = User::where('id', $this->keyword)
            ->orWhere('email', $this->keyword)
            ->orWhere('name', $this->keyword)
            ->with($this->requiredModelRelationships)
            ->get();

        return UserResource::collection($users);
    }
}
