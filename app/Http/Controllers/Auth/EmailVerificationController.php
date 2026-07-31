<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Implements the email verification workflow
 * (17_Authentication_Module.md §12).
 */
final class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        return $request->user()?->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('pages.auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if ($user->markEmailAsVerified()) {
            $this->activate($user);
            event(new Verified($user));
        }

        return redirect()
            ->route('dashboard')
            ->with('status', __('auth.email_verified'));
    }

    public function resend(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', __('auth.verification_sent'));
    }

    /**
     * A verified account transitions out of the pending state.
     */
    private function activate(User $user): void
    {
        if ($user->status === UserStatus::PendingVerification) {
            $user->forceFill(['status' => UserStatus::Active])->save();
        }
    }
}
