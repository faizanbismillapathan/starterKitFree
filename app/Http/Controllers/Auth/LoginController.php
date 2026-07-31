<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginData;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\AccountLockedException;
use App\Exceptions\InvalidCredentialsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Handles session creation and termination.
 *
 * Controllers receive the request, delegate to the service and return a
 * response (02_Project_Rules.md §7).
 */
final class LoginController extends Controller
{
    public function __construct(private readonly AuthenticationService $authentication) {}

    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $this->authentication->login(LoginData::fromRequest($request), $request);
        } catch (AccountLockedException|InvalidCredentialsException|AccountInactiveException $exception) {
            throw ValidationException::withMessages([
                'email' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('status', __('auth.signed_in'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authentication->logout($request);

        return redirect()
            ->route('login')
            ->with('status', __('auth.signed_out'));
    }
}
