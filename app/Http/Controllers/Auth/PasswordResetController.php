<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Implements the forgot and reset password workflows
 * (17_Authentication_Module.md §10–§11).
 */
final class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function sendLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return back()->with('status', __($status));
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'passwordRequirements' => StrongPassword::describe(),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()
            ->route('login')
            ->with('status', __($status));
    }
}
