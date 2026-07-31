<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\ResponseBuilder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Configurable rate limits for authentication-sensitive endpoints
 * (10_Security_Architecture.md §18).
 */
final class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerAuthLimiter();
        $this->registerVerificationLimiter();
        $this->registerApiLimiter();
    }

    private function registerAuthLimiter(): void
    {
        RateLimiter::for('auth', function (Request $request): Limit {
            $key = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinutes(
                (int) config('starter_kit.auth.lockout.decay_minutes'),
                (int) config('starter_kit.auth.lockout.max_attempts'),
            )->by($key)->response($this->tooManyRequests());
        });
    }

    private function registerVerificationLimiter(): void
    {
        RateLimiter::for('verification', fn (Request $request): Limit => Limit::perMinute(6)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip())
            ->response($this->tooManyRequests()));
    }

    private function registerApiLimiter(): void
    {
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip())
            ->response($this->tooManyRequests()));
    }

    /**
     * Shared throttled response honouring the documented JSON envelope.
     */
    private function tooManyRequests(): callable
    {
        return function (Request $request, array $headers) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseBuilder::error(__('errors.too_many_requests'), status: 429)
                    ->withHeaders($headers);
            }

            return response()->view('errors.429', [], 429)->withHeaders($headers);
        };
    }
}
