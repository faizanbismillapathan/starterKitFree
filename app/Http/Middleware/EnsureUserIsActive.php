<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Terminates sessions whose account has since been deactivated or suspended.
 *
 * Authorisation is enforced on every request, never only at sign-in
 * (10_Security_Architecture.md §5).
 */
final class EnsureUserIsActive
{
    public function __construct(private readonly AuthenticationService $authentication) {}

    public function handle(Request $request, Closure $next): BaseResponse
    {
        $user = $request->user();

        if ($user !== null && ! $user->canAuthenticate()) {
            $this->authentication->logout($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.account_inactive'),
                    'errors' => (object) [],
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => __('auth.account_inactive')]);
        }

        return $next($request);
    }
}
