<?php

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
    Route::get('manage', [App\Http\Controllers\PublisherController::class, 'manage'])
        ->name('publisher.manage');
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

/**
 * --------------------------------
 * FEATURE ACCOUNT TYPE
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account-type')->group(function () {
    // Index
    Route::get('manage', [App\Http\Controllers\AccountTypeController::class, 'manage'])
        ->name('accountType.manage');
    // Store
    Route::post('{publisher}', [App\Http\Controllers\AccountTypeController::class, 'store'])
        ->name('accountType.store');
    // Show
    Route::get('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'show'])
        ->name('accountType.show');
    // Update
    Route::put('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'update'])
        ->name('accountType.update');
    // Destroy
    Route::delete('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'destroy'])
        ->name('accountType.destroy');
});
