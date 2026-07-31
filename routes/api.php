<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Support\ResponseBuilder;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Every endpoint is versioned and returns the standard JSON envelope
| (09_API_Architecture.md §3, §11).
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('health', fn () => ResponseBuilder::success([
        'status' => 'ok',
        'version' => config('starter_kit.version'),
        'edition' => config('starter_kit.edition'),
    ], __('errors.health_ok')))->name('health');

    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::middleware('permission:dashboard.view')->group(function (): void {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('dashboard/statistics', [DashboardController::class, 'statistics'])
                ->name('dashboard.statistics');
            Route::get('dashboard/charts', [DashboardController::class, 'charts'])
                ->name('dashboard.charts');
        });

        Route::get('profile', [ProfileController::class, 'show'])
            ->middleware('permission:profile.view')
            ->name('profile.show');

        Route::put('profile', [ProfileController::class, 'update'])
            ->middleware('permission:profile.update')
            ->name('profile.update');

        Route::get('profile/login-history', [ProfileController::class, 'loginHistory'])
            ->middleware('permission:login_history.view')
            ->name('profile.login-history');
    });
});
