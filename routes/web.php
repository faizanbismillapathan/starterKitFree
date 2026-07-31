<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Authenticated application routes. Authentication and profile routes live in
| dedicated files (02_Project_Rules.md §14).
|
*/

Route::redirect('/', '/dashboard')->name('home');

Route::post('theme', [ThemeController::class, 'update'])->name('theme.update');

Route::middleware(['auth', 'verified', 'active'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
});
