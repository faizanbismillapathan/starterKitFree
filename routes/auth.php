<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Implements 17_Authentication_Module.md. Sensitive endpoints are rate limited
| as required by §20.
|
*/

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');

    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:auth')
        ->name('login.store');

    Route::get('register', [RegisterController::class, 'create'])->name('register');

    Route::post('register', [RegisterController::class, 'store'])
        ->middleware('throttle:auth')
        ->name('register.store');

    Route::get('forgot-password', [PasswordResetController::class, 'requestForm'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:auth')
        ->name('password.email');

    Route::get('reset-password/{token}', [PasswordResetController::class, 'resetForm'])
        ->name('password.reset');

    Route::post('reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:auth')
        ->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:verification'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:verification')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmPasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmPasswordController::class, 'store'])
        ->middleware('throttle:auth')
        ->name('password.confirm.store');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
});
