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
use App\Http\Requests\Request;
use App\Models\Role;
use App\Http\Resources\RoleResource;

Route::get('test', function (Request $request) {
    $game = Role::first();
    $game->accountTypes;
    return new RoleResource($game);
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
    // Show
    Route::get('{game}', [GameController::class, 'show'])
        ->name('game.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('', [GameController::class, 'store'])
            ->middleware('can:create,App\Models\Game')
            ->name('game.store');
        // Update
        Route::put('{game}', [GameController::class, 'update'])
            ->middleware('can:update,game')
            ->name('game.update');
        // Destroy
        // Route::delete('{game}', [GameController::class, 'destroy'])
        //     ->middleware('can:delete,game')
        //     ->name('game.destroy');
        // allow discount code
        Route::post('allow-discount-code/{game}/{discountCode}', [GameController::class, 'allowDiscountCode'])
            ->middleware('can:allowDiscountCode,game,discountCode')
            ->name('game.allow-discount-code');
        // get usable game to create an account
        Route::get('all/usable-to-create-account', [GameController::class, 'getUsableGame'])
            ->name('game.get-usable-games');
    });
});

// ====================================================
// Game info routes
// ====================================================
use App\Http\Controllers\GameInfoController;

Route::prefix('game-info')->group(function () {
    // Index
    Route::get('{game}', [GameInfoController::class, 'index'])
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
    // Index
    Route::get('', [AccountTypeController::class, 'index'])
        ->name('account-type.index');
    // Show
    Route::get('{accountType}', [AccountTypeController::class, 'show'])
        ->name('account-type.show');
    // Calculate fee for an cost
    Route::get('{accountType}/calculate-fee', [AccountTypeController::class, 'calculateFee'])
        ->name('account-type.calculate-fee');

    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{game}', [AccountTypeController::class, 'store'])
            ->middleware('can:create,App\Models\AccountType,game')
            ->name('account-type.store');
        // Update
        Route::put('{accountType}', [AccountTypeController::class, 'update'])
            ->middleware('can:update,accountType')
            ->name('account-type.update');
        // Destroy
        // Route::delete('{accountType}', [AccountTypeController::class, 'destroy'])
        //     ->middleware('can:delete,accountType')
        //     ->name('account-type.destroy');
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
    // Show
    Route::get('{account}', [AccountController::class, 'show'])
        ->name('account.show');

    // Routes required verified auth
    Route::middleware(['auth', 'verified'])->group(function () {
        // Store
        Route::post('{accountType}', [AccountController::class, 'store'])
            ->middleware('can:create,App\Models\Account,accountType')
            ->name('account.store');
        // approve
        Route::post('approve/{account}', [AccountController::class, 'approve'])
            ->middleware('can:approve,account')
            ->name('account.approve');
        // Update
        Route::put('{account}', [AccountController::class, 'update'])
            ->middleware('can:update,account')
            ->name('account.update');
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

// ====================================================
// Account trading routes
// ====================================================
use App\Http\Controllers\AccountTradingController;

Route::prefix('account-trading')->group(function () {
    // Get calculated detail price
    Route::get('detail-price/{account}', [AccountTradingController::class, 'calculateDetailPrice'])
        ->name('account-trading.calculate-detail-price');

    Route::middleware(['auth', 'verified'])->group(function () {
        // buy
        Route::post('buy/{account}', [AccountTradingController::class, 'buy'])
            ->middleware('can:buy,account')
            ->name('account-trading.buy');
    });
});

// ====================================================
// Discount code routes
// ====================================================
use App\Http\Controllers\DiscountCodeController;

Route::prefix('discount-code')->group(function () {
    // show
    Route::get('{discountCode}', [DiscountCodeController::class, 'show'])
        ->name('discount-code.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        // store
        Route::post('', [DiscountCodeController::class, 'store'])
            ->middleware('can:create,App\Models\DiscountCode')
            ->name('discount-code.store');
        // update
        Route::put('{discountCode}', [DiscountCodeController::class, 'update'])
            ->middleware('can:update,discountCode')
            ->name('discount-code.update');
        // destroy
        Route::delete('{discountCode}', [DiscountCodeController::class, 'destroy'])
            ->middleware('can:delete,discountCode')
            ->name('discount-code.destroy');
    });
});

// ====================================================
// Discount code trading routes
// ====================================================
use App\Http\Controllers\DiscountCodeTradingController;

Route::prefix('discount-code-trading')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        // buy
        Route::post('{discountCode}', [DiscountCodeTradingController::class, 'buy'])
            ->middleware('can:buy,discountCode')
            ->name('discount-code-trading.buy');
    });
});
