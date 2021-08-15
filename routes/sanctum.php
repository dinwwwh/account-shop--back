<?php

use Illuminate\Support\Facades\Route;
//

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
use App\Http\Requests\Request;
use App\Helpers\ValidationHelper;

Route::post('test', function (Request $request) {
    $request->validate(ValidationHelper::parseRulesByArray('array', [
        'rootRules' => ['array', 'keys:string,min:1,max:50'],
        '*' => ['array', 'keys:integer'],
        '*.*' => ['integer', 'min:0', 'max:100'],
    ]));

    dd($request->array);
});

// ====================================================
// Config routes
// ====================================================
use App\Http\Controllers\ConfigController;

Route::prefix('config')->group(function () {
    Route::get('public', [ConfigController::class, 'getPublicConfigs'])
        ->name('config.get-public-configs');
});

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
Route::prefix('user')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        // Find user by search keyword
        Route::get('search', [UserController::class, 'search'])
            ->name('user.search');
    });
});

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
Route::get('profile', [AuthController::class, 'profile'])
    ->middleware('auth')
    ->name('auth.profile');
// Update password
Route::patch('password', [AuthController::class, 'updatePassword'])
    ->middleware(['auth', 'verified', 'password.confirm-directly'])
    ->name('auth.update-password');
// Get ability of auth
Route::get('can/{ability}', [AuthController::class, 'can'])
    ->middleware('auth')
    ->name('auth.can');

// ====================================================
// Role routes
// ====================================================
use App\Http\Controllers\RoleController;

Route::prefix('role')->group(function () {
    Route::get('', [RoleController::class, 'index'])
        ->name('role.index');
});

// ====================================================
// Game routes
// ====================================================
use App\Http\Controllers\GameController;

Route::prefix('game')->group(function () {
    // Index
    Route::get('', [GameController::class, 'index'])
        ->name('game.index');

    // Store
    Route::post('', [GameController::class, 'store'])
        ->middleware(['auth', 'verified', 'can:create,App\Models\Game'])
        ->name('game.store');
    // get usable game to create an account
    Route::get('all/usable', [GameController::class, 'getUsableGame'])
        ->middleware(['auth', 'verified'])
        ->name('game.get-usable');

    Route::prefix('{game}')->group(function () {
        // Show
        Route::get('', [GameController::class, 'show'])
            ->name('game.show');
        // Update
        Route::put('', [GameController::class, 'update'])
            ->middleware(['auth', 'verified', 'can:update,game'])
            ->name('game.update');
        // Destroy
        // Route::delete('', [GameController::class, 'destroy'])
        //     ->middleware(['auth', 'verified', 'can:delete,game'])
        //     ->name('game.destroy');
        Route::prefix('account')->group(function () {
            // Get accounts of this game
            Route::get('', [GameController::class, 'getAccounts'])
                ->name('game.account.index');
        });
    });
});

// ====================================================
// Game info routes
// ====================================================
use App\Http\Controllers\GameInfoController;

Route::prefix('game-info')->group(function () {
    // Index
    Route::get('', [GameInfoController::class, 'index'])
        ->name('game-info.index');
    // Show
    Route::get('show/{gameInfo}', [GameInfoController::class, 'show'])
        ->name('game-info.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{game}', [GameInfoController::class, 'store'])
            ->middleware('can:create,App\Models\GameInfo,game')
            ->name('game-info.store');
        // Update
        Route::put('{gameInfo}', [GameInfoController::class, 'update'])
            ->middleware('can:update,gameInfo')
            ->name('game-info.update');
        // Destroy
        Route::delete('{gameInfo}', [GameInfoController::class, 'destroy'])
            ->middleware('can:delete,gameInfo')
            ->name('game-info.destroy');
    });
});

// ====================================================
// Account type routes
// ====================================================
use App\Http\Controllers\AccountTypeController;

Route::prefix('account-type')->group(function () {

    Route::get('', [AccountTypeController::class, 'index'])
        ->name('account-type.index');

    Route::get('{accountType}', [AccountTypeController::class, 'show'])
        ->name('account-type.show');

    Route::post('{game}', [AccountTypeController::class, 'store'])
        ->middleware(['auth', 'verified', 'can:create,App\Models\AccountType,game'])
        ->name('account-type.store');

    Route::prefix('{accountType}')->group(function () {
        Route::get('calculate-fee', [AccountTypeController::class, 'calculateFee'])
            ->name('account-type.calculate-fee');
        Route::put('', [AccountTypeController::class, 'update'])
            ->middleware(['auth', 'verified', 'can:update,accountType'])
            ->name('account-type.update');

        Route::prefix('coupon')->group(function () {
            Route::prefix('{coupon}')->group(function () {
                Route::post('', [AccountTypeController::class, 'attachCoupon'])
                    ->middleware(['auth', 'verified', 'can:attach-coupon,accountType'])
                    ->name('account-type.coupon.attach');
                Route::delete('', [AccountTypeController::class, 'detachCoupon'])
                    ->middleware(['auth', 'verified', 'can:detach-coupon,accountType'])
                    ->name('account-type.coupon.detach');
            });
        });
    });
});

// ====================================================
// Account info routes
// ====================================================
use App\Http\Controllers\AccountInfoController;

Route::prefix('account-info')->group(function () {
    // Index
    Route::get('', [AccountInfoController::class, 'index'])
        ->name('account-info.index');
    // Show
    Route::get('{accountInfo}', [AccountInfoController::class, 'show'])
        ->name('account-info.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{accountType}', [AccountInfoController::class, 'store'])
            ->middleware('can:create,App\Models\AccountInfo,accountType')
            ->name('account-info.store');
        // Update
        Route::put('{accountInfo}', [AccountInfoController::class, 'update'])
            ->middleware('can:update,accountInfo')
            ->name('account-info.update');
        // Destroy
        // Route::delete('{accountInfo}', [AccountInfoController::class, 'destroy'])
        //     ->name('account-info.destroy');
    });
});

// ====================================================
// Account action routes
// ====================================================
use App\Http\Controllers\AccountActionController;

Route::prefix('account-action')->group(function () {
    // Index
    Route::get('', [AccountActionController::class, 'index'])
        ->name('account-action.index');
    // Show
    Route::get('{accountAction}', [AccountActionController::class, 'show'])
        ->name('account-action.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{accountType}', [AccountActionController::class, 'store'])
            ->middleware('can:create,App\Models\AccountAction,accountType')
            ->name('account-action.store');
        // Update
        Route::put('{accountAction}', [AccountActionController::class, 'update'])
            ->middleware('can:update,accountAction')
            ->name('account-action.update');
        // Destroy
        // Route::delete('{accountAction}', [AccountActionController::class, 'destroy'])
        //     ->name('account-action.destroy');
    });
});

// ====================================================
// Account routes
// ====================================================
use App\Http\Controllers\AccountController;

Route::prefix('account')->group(function () {
    // Index
    Route::get('', [AccountController::class, 'index'])
        ->name('account.index');

    // Routes required verified auth
    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{accountType}', [AccountController::class, 'store'])
            ->middleware('can:create,App\Models\Account,accountType')
            ->name('account.store');
        // start approving
        Route::post('start-approving/{account}', [AccountController::class, 'startApproving'])
            ->middleware('can:startApproving,account')
            ->name('account.start-approving');
        // end approving
        Route::post('end-approving/{account}', [AccountController::class, 'endApproving'])
            ->middleware('can:endApproving,account')
            ->name('account.end-approving');
        // Update account infos
        Route::patch('{account}/account-infos', [AccountController::class, 'updateAccountInfos'])
            ->middleware('can:update-account-infos,account')
            ->name('account.update-account-infos');
        // Update game infos
        Route::patch('{account}/game-infos', [AccountController::class, 'updateGameInfos'])
            ->middleware('can:update-game-infos,account')
            ->name('account.update-game-infos');
        // Update login infos
        Route::patch('{account}/login-infos', [AccountController::class, 'updateLoginInfos'])
            ->middleware('can:update-login-infos,account')
            ->name('account.update-login-infos');
        // Update images
        Route::patch('{account}/images', [AccountController::class, 'updateImages'])
            ->middleware('can:update-images,account')
            ->name('account.update-images');
        // Update cost
        Route::patch('{account}/cost', [AccountController::class, 'updateCost'])
            ->middleware('can:update-cost,account')
            ->name('account.update-cost');
        // Destroy
        // Route::delete('{account}', [AccountController::class, 'destroy'])
        //     ->name('account.destroy');

        // Routes used to manage accounts
        Route::prefix('manage')->group(function () {
            // Get full accounts can manage
            Route::get('index', [AccountController::class, 'manage'])
                ->name('account.manage.index');
        });
    });

    Route::prefix('{account}')->group(function () {
        Route::get('', [AccountController::class, 'show'])
            ->name('account.show');
        Route::get('price', [AccountController::class, 'getPrice'])
            ->middleware(['auth', 'verified'])
            ->name('account.get-price');
        Route::patch('buy', [AccountController::class, 'buy'])
            ->middleware(['auth', 'verified', 'can:buy,account'])
            ->name('account.buy');
    });
});

// ====================================================
// Account fee routes
// ====================================================
use App\Http\Controllers\AccountFeeController;

Route::prefix('account-fee')->group(function () {

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{accountType}', [AccountFeeController::class, 'store'])
            ->middleware('can:create,App\Models\AccountFee,accountType')
            ->name('account-fee.store');
        // Update
        Route::put('{accountFee}', [AccountFeeController::class, 'update'])
            ->middleware('can:update,accountFee')
            ->name('account-fee.update');
        // Destroy
        Route::delete('{accountFee}', [AccountFeeController::class, 'destroy'])
            ->middleware('can:delete,accountFee')
            ->name('account-fee.destroy');
    });
});

use App\Http\Controllers\RechargePhonecardController;

Route::prefix('recharge-phonecard')->group(function () {
    Route::get('', [RechargePhonecardController::class, 'index'])
        ->name('recharge-phonecard.index');
    Route::post('', [RechargePhonecardController::class, 'store'])
        ->middleware(['auth', 'verified'])
        ->name('recharge-phonecard.store');
    Route::get('approvable', [RechargePhonecardController::class, 'getApprovable'])
        ->middleware(['auth', 'verified'])
        ->name('recharge-phonecard.get-approvable');

    Route::prefix('{rechargePhonecard}')->group(function () {
        Route::get('', [RechargePhonecardController::class, 'show'])
            ->name('recharge-phonecard.show');
        Route::patch('start-approving', [RechargePhonecardController::class, 'startApproving'])
            ->middleware(['auth', 'verified', 'can:start-approving,rechargePhonecard'])
            ->name('recharge-phonecard.start-approving');
        Route::patch('end-approving', [RechargePhonecardController::class, 'endApproving'])
            ->middleware(['auth', 'verified', 'can:end-approving,rechargePhonecard'])
            ->name('recharge-phonecard.end-approving');
    });
});

/**
 * Coupon routes
 *
 */

use App\Http\Controllers\CouponController;

Route::prefix('coupon')->group(function () {

    Route::get('', [CouponController::class, 'index'])
        ->name('coupon.index');

    Route::post('', [CouponController::class, 'store'])
        ->middleware(['auth', 'verified', 'can:create,App\Models\Coupon'])
        ->name('coupon.store');

    Route::prefix('{coupon}')->group(function () {
        Route::get('', [CouponController::class, 'show'])
            ->name('coupon.show');
        Route::put('', [CouponController::class, 'update'])
            ->middleware(['auth', 'verified', 'can:update,coupon'])
            ->name('coupon.update');
        Route::delete('', [CouponController::class, 'destroy'])
            ->middleware(['auth', 'verified', 'can:delete,coupon'])
            ->name('coupon.destroy');
    });
});
