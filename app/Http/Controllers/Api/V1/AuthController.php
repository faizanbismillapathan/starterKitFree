<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\Auth\RegisterUserData;
use App\Enums\LoginStatus;
use App\Exceptions\AccountInactiveException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Actions\Auth\RecordLoginAttemptAction;
use App\Contracts\UserRepositoryInterface;
use App\Services\AuthenticationService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Token based authentication for API clients (09_API_Architecture.md §7).
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authentication,
        private readonly UserRepositoryInterface $users,
        private readonly RecordLoginAttemptAction $recordAttempt,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authentication->register(RegisterUserData::fromRequest($request));

        return ResponseBuilder::created(
            ['user' => new UserResource($user->load('roles'))],
            __('auth.api.registered'),
        );
    }

    /**
     * Issues a personal access token for the supplied credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = mb_strtolower((string) $request->validated('email'));
        $throttleKey = Str::transliterate($email.'|api|'.$request->ip());
        $maxAttempts = (int) config('starter_kit.auth.lockout.max_attempts');

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            return ResponseBuilder::error(
                __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                    'minutes' => (int) ceil(RateLimiter::availableIn($throttleKey) / 60),
                ]),
                status: 429,
            );
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || ! Hash::check((string) $request->validated('password'), $user->password)) {
            RateLimiter::hit(
                $throttleKey,
                (int) config('starter_kit.auth.lockout.decay_minutes') * 60,
            );

            $this->recordAttempt->execute(
                status: LoginStatus::Failed,
                email: $email,
                user: $user,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if (! $user->canAuthenticate()) {
            throw new AccountInactiveException($user->status);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken(
            name: (string) ($request->input('device_name') ?? 'api-token'),
        );

        $this->recordAttempt->execute(
            status: LoginStatus::Successful,
            email: $email,
            user: $user,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return ResponseBuilder::success([
            'token' => $token->plainTextToken,
            'user' => new UserResource($user->load('roles')),
        ], __('auth.api.authenticated'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ResponseBuilder::success(message: __('auth.api.signed_out'));
    }

    public function me(Request $request): JsonResponse
    {
        return ResponseBuilder::success(
            new UserResource($request->user()->load('roles')),
            __('auth.api.profile_retrieved'),
        );
    }
}
