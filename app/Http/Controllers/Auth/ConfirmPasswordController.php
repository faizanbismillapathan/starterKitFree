<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Re-confirms the account password before sensitive operations.
 */
final class ConfirmPasswordController extends Controller
{
    public function show(): View
    {
        return view('pages.auth.confirm-password');
    }

    public function store(ConfirmPasswordRequest $request): RedirectResponse
    {
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard'));
    }
}
