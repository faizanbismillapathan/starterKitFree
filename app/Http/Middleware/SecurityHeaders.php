<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the browser security headers required by
 * 10_Security_Architecture.md §16.
 *
 * Headers are managed centrally so individual responses cannot omit them. A
 * per-request nonce is generated for the Content Security Policy, allowing the
 * few unavoidable inline scripts to execute without weakening the policy with
 * `unsafe-inline`.
 */
final class SecurityHeaders
{
    /**
     * Container key holding the nonce for the current request.
     */
    public const NONCE_KEY = 'csp-nonce';

    /**
     * Static headers applied to every response.
     *
     * @var array<string, string>
     */
    private const HEADERS = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = $this->generateNonce();

        $response = $next($request);

        foreach (self::HEADERS as $header => $value) {
            $response->headers->set($header, $value, false);
        }

        $response->headers->set(
            'Content-Security-Policy',
            $this->contentSecurityPolicy($nonce),
            false,
        );

        // HSTS is only meaningful over an encrypted connection.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
                false,
            );
        }

        return $response;
    }

    /**
     * Creates the nonce and shares it with the Vite helper so generated tags
     * carry the same value.
     */
    private function generateNonce(): string
    {
        $nonce = Str::random(24);

        app()->instance(self::NONCE_KEY, $nonce);

        Vite::useCspNonce($nonce);

        return $nonce;
    }

    /**
     * Builds the policy for the current request.
     *
     * The Vite development server injects its own client over http/ws, so the
     * local origin is permitted while running in development.
     */
    private function contentSecurityPolicy(string $nonce): string
    {
        /*
         * Alpine.js compiles its expressions at runtime, which the browser
         * treats as evaluating a string as JavaScript. The standard Alpine
         * build therefore requires 'unsafe-eval'; without it every directive
         * throws and the interface stops responding.
         *
         * The nonce and 'strict-dynamic' still restrict which scripts may load
         * in the first place, so untrusted third-party code remains blocked.
         */
        $scriptSources = ["'self'", "'nonce-{$nonce}'", "'strict-dynamic'", "'unsafe-eval'"];

        // Alpine writes element styles directly, which counts as inline CSS.
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'];
        $connectSources = ["'self'"];

        if ($this->viteDevServerIsRunning()) {
            $scriptSources[] = 'http://localhost:5173';
            $scriptSources[] = 'http://127.0.0.1:5173';
            $styleSources[] = 'http://localhost:5173';
            $styleSources[] = 'http://127.0.0.1:5173';
            $connectSources[] = 'http://localhost:5173';
            $connectSources[] = 'http://127.0.0.1:5173';
            $connectSources[] = 'ws://localhost:5173';
            $connectSources[] = 'ws://127.0.0.1:5173';
        }

        $directives = [
            "default-src 'self'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "img-src 'self' data: blob:",
            'font-src \'self\' data: https://fonts.bunny.net',
            'connect-src '.implode(' ', $connectSources),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        return implode('; ', $directives);
    }

    private function viteDevServerIsRunning(): bool
    {
        return ! app()->isProduction()
            && is_file(public_path('hot'));
    }
}
