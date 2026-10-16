<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RechargeRequestController;
use App\Http\Controllers\PlanPurchaseController;
use App\Http\Controllers\Admin\UserSubscriptionController;
use App\Http\Controllers\Admin\DailyProfitController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\UserDailyProfitController;
use App\Http\Controllers\Admin\WalletTransactionController;

use App\Http\Controllers\Admin\DashboardController;




Route::get('/', function () {
    return view('welcome');
});

  Route::get('/home', [HomeController::class, 'index'])->name('index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'create'])->name('login');
    Route::post('/login', [UserAuthController::class, 'store'])->name('login.store');
    Route::get('/register', [UserAuthController::class, 'register'])->name('register');
    Route::post('/register', [UserAuthController::class, 'createAccount'])->name('register.store');
});

Route::middleware(['auth', 'member'])->group(function () {
    Route::post('/logout', [UserAuthController::class, 'destroy'])->name('logout');
    Route::get('/plans', [PlanPurchaseController::class, 'index'])->name('plans.index');
    Route::post('/plans/{plan}/purchase', [PlanPurchaseController::class, 'purchase'])
        ->middleware('throttle:10,1')
        ->name('plans.purchase');
    Route::get('/my-subscriptions', [PlanPurchaseController::class, 'mySubscriptions'])
        ->name('subscriptions.mine');
    Route::get('/my-daily-profits', [UserDailyProfitController::class, 'index'])
        ->name('profits.mine');
    Route::get('/my-withdrawals', [WithdrawalRequestController::class, 'index'])
        ->name('withdrawals.mine');
    Route::post('/my-withdrawals', [WithdrawalRequestController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('withdrawals.store');
});

Route::prefix('admin')->name('admin.')->group(function () {


        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])
                ->middleware('throttle:admin-login')
                ->name('login.store');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

                Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', function () {
            return redirect()->route(
                'admin.subscription-plans.index'
            );
        })->name('dashboard');

        Route::resource(
            'subscription-plans',
            SubscriptionPlanController::class
        );

        Route::resource('users', UserController::class)
    ->only(['index', 'show', 'edit', 'update']);

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('recharges', [RechargeRequestController::class, 'index'])
    ->name('recharges.index');

Route::get('recharges/{recharge}', [RechargeRequestController::class, 'show'])
    ->name('recharges.show');

Route::post('recharges/{recharge}/approve', [RechargeRequestController::class, 'approve'])
    ->name('recharges.approve');

Route::post('recharges/{recharge}/reject', [RechargeRequestController::class, 'reject'])
    ->name('recharges.reject');

Route::get('recharges/{recharge}/proof', [RechargeRequestController::class, 'proof'])
    ->name('recharges.proof');


Route::get('subscriptions', [UserSubscriptionController::class, 'index'])
    ->name('subscriptions.index');

Route::get('subscriptions/{subscription}', [UserSubscriptionController::class, 'show'])
    ->name('subscriptions.show');

    Route::get('daily-profits', [DailyProfitController::class, 'index'])
    ->name('daily-profits.index');

Route::get('daily-profits/{dailyProfit}', [DailyProfitController::class, 'show'])
    ->name('daily-profits.show');

Route::post('daily-profits/{dailyProfit}/approve', [DailyProfitController::class, 'approve'])
    ->name('daily-profits.approve');

Route::post('daily-profits/{dailyProfit}/reject', [DailyProfitController::class, 'reject'])
    ->name('daily-profits.reject');

    Route::get('withdrawals', [WithdrawalController::class, 'index'])
    ->name('withdrawals.index');

Route::get('withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])
    ->name('withdrawals.show');

Route::post('withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])
    ->name('withdrawals.approve');

Route::post('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])
    ->name('withdrawals.reject');

Route::post('withdrawals/{withdrawal}/mark-paid', [WithdrawalController::class, 'markPaid'])
    ->name('withdrawals.mark-paid');

Route::get('wallet-transactions', [WalletTransactionController::class, 'index'])
    ->name('wallet-transactions.index');

Route::get('wallet-transactions/{walletTransaction}', [WalletTransactionController::class, 'show'])
    ->name('wallet-transactions.show');

});