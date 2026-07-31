<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Password changes initiated by the account owner.
 */
final class PasswordController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->profile->changePassword($user, (string) $request->validated('password'));

        if (config('starter_kit.auth.invalidate_sessions_on_password_change')) {
            Auth::logoutOtherDevices((string) $request->validated('password'));
        }

        return back()->with('status', __('profile.password_updated'));
    }
}
