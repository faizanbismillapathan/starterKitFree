<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ThemeMode;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active theme once per request and shares it with every view.
 *
 * Authenticated preferences win over the cookie so the choice follows the user
 * across devices (05_Design_System.md §10).
 */
final class ApplyThemePreference
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        /*
         * The header renders the signed-in user's avatar on every page, so the
         * media relation is loaded once here rather than lazily per component
         * (38_Performance_Guide.md §8).
         */
        if ($user instanceof User) {
            $user->loadMissing('media');
        }

        View::share('themeMode', $this->resolve($request));

        return $next($request);
    }

    private function resolve(Request $request): ThemeMode
    {
        $user = $request->user();

        if ($user instanceof User && $user->theme !== null) {
            return $user->theme;
        }

        $cookie = $request->cookie((string) config('theme.cookie'));

        if (is_string($cookie) && in_array($cookie, ThemeMode::values(), true)) {
            return ThemeMode::from($cookie);
        }

        return ThemeMode::from((string) config('theme.default_mode'));
    }
}
