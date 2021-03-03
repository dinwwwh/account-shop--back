<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

\Auth::attempt(['email' => 'dinhdjj@gmail.com', 'password' => '12345678']);

/**
 * --------------------------------
 * FEATURE RULE
 * --------------------------------
 * Include infos to make rules to validate in font-end and back-end.
 */
Route::prefix('rule')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\RuleController::class, 'index'])
        ->name('rule.index');
    // Store
    Route::post('', [App\Http\Controllers\RuleController::class, 'store'])
        ->name('rule.store');
    // Show
    Route::get('{rule}', [App\Http\Controllers\RuleController::class, 'show'])
        ->name('rule.show');
    // Update
    Route::put('{rule}', [App\Http\Controllers\RuleController::class, 'update'])
        ->name('rule.update');
    // Destroy
    Route::delete('{rule}', [App\Http\Controllers\RuleController::class, 'destroy'])
        ->name('rule.destroy');
});


/**
 * --------------------------------
 * FEATURE PUBLISHER
 * --------------------------------
 * Contain infos of publisher.
 */
Route::prefix('publisher')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\PublisherController::class, 'index'])
        ->name('publisher.index');
    // Store
    Route::post('', [App\Http\Controllers\PublisherController::class, 'store'])
        ->name('publisher.store');
    // Show
    Route::get('{publisher}', [App\Http\Controllers\PublisherController::class, 'show'])
        ->name('publisher.show');
    // Update
    Route::put('{publisher}', [App\Http\Controllers\PublisherController::class, 'update'])
        ->name('publisher.update');
    // Destroy
    Route::delete('{publisher}', [App\Http\Controllers\PublisherController::class, 'destroy'])
        ->name('publisher.destroy');
});
