<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Rules\StrongPassword;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles new account creation.
 */
final class RegisterController extends Controller
{
    public function __construct(private readonly AuthenticationService $authentication) {}

    public function create(): View
    {
        $this->assertRegistrationEnabled();

        return view('pages.auth.register', [
            'passwordRequirements' => StrongPassword::describe(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authentication->register(RegisterUserData::fromRequest($request));

        if (! config('starter_kit.auth.email_verification_required')) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()
                ->route('dashboard')
                ->with('status', __('auth.registered'));
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('verification.notice')
            ->with('status', __('auth.verification_sent'));
    }

    /**
     * Registration may be disabled entirely through configuration.
     */
    private function assertRegistrationEnabled(): void
    {
        if (! config('starter_kit.auth.registration_enabled')) {
            throw new NotFoundHttpException;
        }
    }
}
