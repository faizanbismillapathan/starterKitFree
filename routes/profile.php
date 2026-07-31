<?php

declare(strict_types=1);

use App\Http\Controllers\Profile\PasswordController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Profile\SecurityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
|
| Self-service account management for the signed-in user.
|
*/

Route::middleware(['auth', 'verified', 'active'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function (): void {
        Route::get('/', [ProfileController::class, 'edit'])
            ->middleware('permission:profile.view')
            ->name('edit');

        Route::put('/', [ProfileController::class, 'update'])
            ->middleware('permission:profile.update')
            ->name('update');

        Route::post('avatar', [ProfileController::class, 'updateAvatar'])
            ->middleware('permission:media.upload')
            ->name('avatar.store');

        Route::delete('avatar', [ProfileController::class, 'destroyAvatar'])
            ->middleware('permission:media.delete')
            ->name('avatar.destroy');

        Route::put('password', [PasswordController::class, 'update'])
            ->middleware('permission:profile.update')
            ->name('password.update');

        Route::get('security', [SecurityController::class, 'index'])
            ->middleware('permission:sessions.view')
            ->name('security');

        Route::get('login-history', [SecurityController::class, 'loginHistory'])
            ->middleware('permission:login_history.view')
            ->name('login-history');

        Route::delete('sessions/{session}', [SecurityController::class, 'destroySession'])
            ->middleware('permission:sessions.revoke')
            ->name('sessions.destroy');

        Route::delete('sessions', [SecurityController::class, 'destroyOtherSessions'])
            ->middleware('permission:sessions.revoke')
            ->name('sessions.destroy-others');
    });
