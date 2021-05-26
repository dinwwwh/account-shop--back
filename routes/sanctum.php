<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SANCTUM API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "web" middleware group. Enjoy building your API!
|
*/

// ====================================================
// User routes
// ====================================================
use App\Http\Controllers\UserController;

// Register
Route::post('register', [UserController::class, 'register'])
    ->middleware('guest')
    ->name('register');
// Verify email
Route::get('verify/{id}/{hash}', [UserController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');
// Send email verification notification
Route::post('verification-notification', [UserController::class, 'sendEmailVerificationNotification'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// ====================================================
// Password routes
// ====================================================
use App\Http\Controllers\PasswordController;

// forgot password
Route::post('forgot-password', [PasswordController::class, 'forgotPassword'])
    ->middleware('guest')
    ->name('password.email');
// reset password
Route::post('reset-password', [PasswordController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

// ====================================================
// Auth routes
// ====================================================
use App\Http\Controllers\AuthController;

// Login
Route::post('login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');
// Logout
Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
// Show profile
Route::get('profile', [AuthController::class, 'show'])
    ->middleware('auth')
    ->name('auth.profile');

// ====================================================
// Game routes
// ====================================================
Route::prefix('game')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\GameController::class, 'index'])
        ->name('game.index');
    // Show
    Route::get('{game}', [App\Http\Controllers\GameController::class, 'show'])
        ->name('game.show');

    Route::middleware('auth')->group(function () {
        // Store
        Route::post('', [App\Http\Controllers\GameController::class, 'store'])
            ->middleware('can:create,App\Models\Game')
            ->name('game.store');
        // Update
        Route::put('{game}', [App\Http\Controllers\GameController::class, 'update'])
            ->middleware('can:update,game')
            ->name('game.update');
        // Destroy
        // Route::delete('{game}', [App\Http\Controllers\GameController::class, 'destroy'])
        //     ->middleware('can:delete,game')
        //     ->name('game.destroy');
        // allow discount code
        Route::post('allow-discount-code/{game}/{discountCode}', [App\Http\Controllers\GameController::class, 'allowDiscountCode'])
            ->middleware('can:allowDiscountCode,game,discountCode')
            ->name('game.allow-discount-code');
    });
});
