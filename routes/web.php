<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/test-csrf', function () {
    return response(['message' => 'success!!']);
})->name('test.csrf');
Route::post('/test-auth', function () {
    return response(['message' => 'Authentication!!']);
})->middleware('auth')->name('test.auth');

/**
 * Auth Controller
 * -------------------
 */
// Register
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])
    ->name('register');
// Login
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])
    ->name('login');
// Logout
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
