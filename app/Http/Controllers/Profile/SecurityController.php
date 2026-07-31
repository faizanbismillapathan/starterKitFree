<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LogoutOtherDevicesRequest;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\AuthenticationService;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Security centre: active sessions and recent authentication activity
 * (17_Authentication_Module.md §22–§23).
 */
final class SecurityController extends Controller
{
    public function __construct(
        private readonly SessionService $sessions,
        private readonly AuthenticationService $authentication,
        private readonly LoginHistoryRepositoryInterface $histories,
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.profile.security', [
            'user' => $user,
            'sessions' => $this->sessions->forUser($user, $request->session()->getId()),
            'recentLogins' => $this->histories->recentForUser($user, 5),
            'passwordRequirements' => StrongPassword::describe(),
        ]);
    }

    public function loginHistory(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.profile.login-history', [
            'histories' => $this->histories->paginateForUser(
                $user,
                $this->resolvePerPage($request),
            ),
        ]);
    }

    public function destroySession(Request $request, string $session): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->sessions->revoke($user, $session, $request->session()->getId());

        return back()->with('status', __('profile.session_revoked'));
    }

    public function destroyOtherSessions(LogoutOtherDevicesRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authentication->logoutOtherDevices(
            $user,
            (string) $request->validated('password'),
            $request,
        );

        return back()->with('status', __('profile.other_sessions_revoked'));
    }

    /**
     * Honours the documented page size options (13_Table_System.md §19).
     */
    private function resolvePerPage(Request $request): int
    {
        $options = (array) config('starter_kit.pagination.options');
        $requested = (int) $request->integer('per_page');

        return in_array($requested, $options, true)
            ? $requested
            : (int) config('starter_kit.pagination.default');
    }
}
