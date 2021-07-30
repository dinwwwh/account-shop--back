<?php

namespace App\Http\Middleware;

use Closure;
use Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class ConfirmDirectlyPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->validate([
            '_confirmedPassword' => 'required|string'
        ]);

        if (!auth()->check()) {
            throw new AuthenticationException(
                'Unauthenticated',
                [],
                $request->expectsJson() ? null : route('login')
            );
        }

        if (!Hash::check($request->_confirmedPassword, $request->user()->password)) {
            return response()->json([
                'message' => 'Confirmed password is invalid.',
            ], 423);
        }

        return $next($request);
    }
}
