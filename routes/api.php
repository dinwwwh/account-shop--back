<?php

use Illuminate\Support\Facades\Route;
use App\Http\Requests\Request;
// use Illuminate\Support\Carbon;

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

use App\Models\Role;
use App\Models\Permission;

// auth()->attempt(['email' => 'dinhdjj@gmail.com', 'password' => '12345678']);
// auth()->user()->assignRole('tester');
// auth()->user()->assignRole('administrator');
// auth()->user()->refresh();

Route::get('test', function (Request $request) {
    dd(Role::mustBeRole(Role::first()));
});

/**
 * --------------------------------
 * FEATURE PROFILE
 * --------------------------------
 * Info of current user
 */

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
 * FEATURE ACCOUNT TYPE
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account-type')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\AccountTypeController::class, 'index'])
        ->name('account-type.index');
    // Show
    Route::get('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'show'])
        ->name('account-type.show');

    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{game}', [App\Http\Controllers\AccountTypeController::class, 'store'])
            ->middleware('can:create,App\Models\AccountType,game')
            ->name('account-type.store');
        // Update
        Route::put('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'update'])
            ->middleware('can:update,accountType')
            ->name('account-type.update');
        // Destroy
        // Route::delete('{accountType}', [App\Http\Controllers\AccountTypeController::class, 'destroy'])
        //     ->middleware('can:delete,accountType')
        //     ->name('account-type.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE ACCOUNT FEE
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account-fee')->group(function () {
    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{accountType}', [App\Http\Controllers\AccountFeeController::class, 'store'])
            ->middleware('can:create,App\Models\AccountFee,accountType')
            ->name('account-fee.store');
        // Update
        Route::put('{accountFee}', [App\Http\Controllers\AccountFeeController::class, 'update'])
            ->middleware('can:update,accountFee')
            ->name('account-fee.update');
        // Destroy
        Route::delete('{accountFee}', [App\Http\Controllers\AccountFeeController::class, 'destroy'])
            ->middleware('can:delete,accountFee')
            ->name('account-fee.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE ACCOUNT INFO
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account-info')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\AccountInfoController::class, 'index'])
        ->name('account-info.index');
    // Show
    Route::get('{accountInfo}', [App\Http\Controllers\AccountInfoController::class, 'show'])
        ->name('account-info.show');

    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{accountType}', [App\Http\Controllers\AccountInfoController::class, 'store'])
            ->middleware('can:create,App\Models\AccountInfo,accountType')
            ->name('account-info.store');
        // Update
        Route::put('{accountInfo}', [App\Http\Controllers\AccountInfoController::class, 'update'])
            ->middleware('can:update,accountInfo')
            ->name('account-info.update');
        // Destroy
        // Route::delete('{accountInfo}', [App\Http\Controllers\AccountInfoController::class, 'destroy'])
        //     ->name('account-info.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE ACCOUNT ACTION
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account-action')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\AccountActionController::class, 'index'])
        ->name('account-action.index');
    // Show
    Route::get('{accountAction}', [App\Http\Controllers\AccountActionController::class, 'show'])
        ->name('account-action.show');


    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{accountType}', [App\Http\Controllers\AccountActionController::class, 'store'])
            ->middleware('can:create,App\Models\AccountAction,accountType')
            ->name('account-action.store');
        // Update
        Route::put('{accountAction}', [App\Http\Controllers\AccountActionController::class, 'update'])
            ->middleware('can:update,accountAction')
            ->name('account-action.update');
        // Destroy
        // Route::delete('{accountAction}', [App\Http\Controllers\AccountActionController::class, 'destroy'])
        //     ->name('account-action.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE GAME
 * --------------------------------
 * Contain infos of account type.
 */
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

/**
 * --------------------------------
 * FEATURE GAME INFO
 * --------------------------------
 */
Route::prefix('game-info')->group(function () {
    // Index
    Route::get('{game}', [App\Http\Controllers\GameInfoController::class, 'index'])
        ->name('game-info.index');
    // Show
    Route::get('show/{gameInfo}', [App\Http\Controllers\GameInfoController::class, 'show'])
        ->name('game-info.show');

    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{game}', [App\Http\Controllers\GameInfoController::class, 'store'])
            ->middleware('can:create,App\Models\GameInfo,game')
            ->name('game-info.store');
        // Update
        Route::put('{gameInfo}', [App\Http\Controllers\GameInfoController::class, 'update'])
            ->middleware('can:update,gameInfo')
            ->name('game-info.update');
        // Destroy
        Route::delete('{gameInfo}', [App\Http\Controllers\GameInfoController::class, 'destroy'])
            ->middleware('can:delete,gameInfo')
            ->name('game-info.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE ACCOUNT
 * --------------------------------
 * Contain infos of account type.
 */
Route::prefix('account')->group(function () {
    // Index
    Route::get('', [App\Http\Controllers\AccountController::class, 'index'])
        ->name('account.index');
    // Show
    Route::get('{account}', [App\Http\Controllers\AccountController::class, 'show'])
        ->name('account.show');

    Route::middleware('auth')->group(function () {
        // Store
        Route::post('{accountType}', [App\Http\Controllers\AccountController::class, 'store'])
            ->middleware('can:create,App\Models\Account,accountType')
            ->name('account.store');
        // approve
        Route::post('approve/{account}', [App\Http\Controllers\AccountController::class, 'approve'])
            ->middleware('can:approve,account')
            ->name('account.approve');
        // Update
        Route::put('{account}', [App\Http\Controllers\AccountController::class, 'update'])
            ->middleware('can:update,account')
            ->name('account.update');
        // Destroy
        // Route::delete('{account}', [App\Http\Controllers\AccountController::class, 'destroy'])
        //     ->name('account.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE ACCOUNT TRADING
 * --------------------------------
 *
 */
Route::prefix('account-trading')->group(function () {
    // calculate detail price
    Route::post('detail-price/{account}', [App\Http\Controllers\AccountTradingController::class, 'calculateDetailPrice'])
        ->name('account-trading.calculate-detail-price');

    Route::middleware('auth')->group(function () {
        // buy
        Route::post('buy/{account}', [App\Http\Controllers\AccountTradingController::class, 'buy'])
            ->middleware('can:buy,account')
            ->name('account-trading.buy');
    });
});

/**
 * --------------------------------
 * FEATURE DISCOUNT CODE
 * --------------------------------
 *
 */
Route::prefix('discount-code')->group(function () {
    // show
    Route::get('{discountCode}', [App\Http\Controllers\DiscountCodeController::class, 'show'])
        ->name('discount-code.show');

    Route::middleware('auth')->group(function () {
        // store
        Route::post('', [App\Http\Controllers\DiscountCodeController::class, 'store'])
            ->middleware('can:create,App\Models\DiscountCode')
            ->name('discount-code.store');
        // update
        Route::put('{discountCode}', [App\Http\Controllers\DiscountCodeController::class, 'update'])
            ->middleware('can:update,discountCode')
            ->name('discount-code.update');
        // destroy
        Route::delete('{discountCode}', [App\Http\Controllers\DiscountCodeController::class, 'destroy'])
            ->middleware('can:delete,discountCode')
            ->name('discount-code.destroy');
    });
});

/**
 * --------------------------------
 * FEATURE DISCOUNT CODE TRADING
 * --------------------------------
 *
 */
Route::prefix('discount-code-trading')->group(function () {
    Route::middleware('auth')->group(function () {
        // buy
        Route::post('{discountCode}', [App\Http\Controllers\DiscountCodeTradingController::class, 'buy'])
            ->middleware('can:buy,discountCode')
            ->name('discount-code-trading.buy');
    });
});
