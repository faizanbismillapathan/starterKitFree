<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Auth\CreateUserAction;
use App\Actions\Auth\RecordLoginAttemptAction;
use App\Contracts\LoginHistoryRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterUserData;
use App\Enums\LoginStatus;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Events\UserRegistered;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\AccountLockedException;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Coordinates the authentication workflows described in
 * 17_Authentication_Module.md.
 *
 * The service owns business rules, transactions and event dispatching;
 * controllers merely delegate to it (06_System_Architecture.md §5).
 */
final readonly class AuthenticationService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private LoginHistoryRepositoryInterface $histories,
        private CreateUserAction $createUser,
        private RecordLoginAttemptAction $recordAttempt,
    ) {}

    /**
     * Registration workflow (17_Authentication_Module.md §8).
     */
    public function register(RegisterUserData $data): User
    {
        $user = DB::transaction(fn (): User => $this->createUser->execute($data));

        event(new UserRegistered($user));

        if (config('starter_kit.auth.email_verification_required')) {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }

    /**
     * Login workflow (17_Authentication_Module.md §7).
     *
     * @throws AccountLockedException
     * @throws InvalidCredentialsException
     * @throws AccountInactiveException
     */
    public function login(LoginData $data, Request $request): User
    {
        $this->guardAgainstBruteForce($data);

        $user = $this->users->findByEmail($data->email);

        if ($user === null || ! $this->guard()->validate($data->credentials())) {
            $this->registerFailure($data, $user);

            throw new InvalidCredentialsException;
        }

        if (! $user->canAuthenticate()) {
            $this->recordAttempt->execute(
                status: LoginStatus::Locked,
                email: $data->email,
                user: $user,
                ipAddress: $data->ipAddress,
                userAgent: $data->userAgent,
            );

            throw new AccountInactiveException($user->status);
        }

        $this->guard()->login($user, $data->remember);
        $request->session()->regenerate();

        RateLimiter::clear($this->throttleKey($data));

        $this->recordSuccess($user, $data, $request->session()->getId());

        event(new UserLoggedIn($user, $data->ipAddress));

        return $user;
    }

    /**
     * Logout workflow (17_Authentication_Module.md §9).
     */
    public function logout(Request $request): void
    {
        $user = $this->guard()->user();
        $sessionId = $request->session()->getId();

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->histories->markLoggedOut($sessionId);

        if ($user instanceof User) {
            event(new UserLoggedOut($user));
        }
    }

    /**
     * Revokes every session belonging to the user except the current one.
     */
    public function logoutOtherDevices(User $user, string $password, Request $request): void
    {
        Auth::logoutOtherDevices($password);

        $currentSessionId = $request->session()->getId();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->where('id', '!=', $currentSessionId)
                ->delete();
        }
    }

    /**
     * Brute force protection (17_Authentication_Module.md §19–§20).
     *
     * @throws AccountLockedException
     */
    private function guardAgainstBruteForce(LoginData $data): void
    {
        $maxAttempts = (int) config('starter_kit.auth.lockout.max_attempts');

        if (! RateLimiter::tooManyAttempts($this->throttleKey($data), $maxAttempts)) {
            return;
        }

        throw new AccountLockedException(
            RateLimiter::availableIn($this->throttleKey($data)),
        );
    }

    private function registerFailure(LoginData $data, ?User $user): void
    {
        RateLimiter::hit(
            $this->throttleKey($data),
            (int) config('starter_kit.auth.lockout.decay_minutes') * 60,
        );

        $this->recordAttempt->execute(
            status: LoginStatus::Failed,
            email: $data->email,
            user: $user,
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent,
        );
    }

    private function recordSuccess(User $user, LoginData $data, string $sessionId): void
    {
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $data->ipAddress,
            'login_count' => $user->login_count + 1,
        ])->save();

        $this->recordAttempt->execute(
            status: LoginStatus::Successful,
            email: $data->email,
            user: $user,
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent,
            sessionId: $sessionId,
        );
    }

    /**
     * Rate limit key combining credentials and origin address.
     */
    private function throttleKey(LoginData $data): string
    {
        return Str::transliterate(
            Str::lower($data->email).'|'.($data->ipAddress ?? 'unknown'),
        );
    }

    private function guard(): StatefulGuard
    {
        /** @var StatefulGuard $guard */
        $guard = Auth::guard('web');

        return $guard;
    }
}
