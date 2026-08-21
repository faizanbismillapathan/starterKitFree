<?php

declare(strict_types=1);

use App\Exceptions\AccountInactiveException;
use App\Exceptions\AccountLockedException;
use App\Http\Middleware\ApplyThemePreference;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use App\Support\ResponseBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/profile.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Railway / Heroku / any load balancer terminates TLS at the edge and
         * forwards plain HTTP, passing the real scheme in X-Forwarded-Proto.
         * Without trusting it, every generated URL says http:// and the browser
         * blocks the assets as mixed content.
         */
    $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            ApplyThemePreference::class,
            SecurityHeaders::class,
        ]);

        $middleware->api(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn (): string => route('login'));
        $middleware->redirectUsersTo(fn (): string => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * API consumers always receive the documented JSON envelope
         * (09_API_Architecture.md §23). Internal details are never exposed.
         */
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return match (true) {
                $exception instanceof ValidationException => ResponseBuilder::error(
                    __('errors.validation_failed'),
                    $exception->errors(),
                    422,
                ),
                /*
                 * A recognised account whose status forbids sign-in is a
                 * refusal, not a server fault (17_Authentication_Module.md §13).
                 */
                $exception instanceof AccountInactiveException => ResponseBuilder::error(
                    $exception->getMessage(),
                    status: 403,
                ),
                $exception instanceof AccountLockedException => ResponseBuilder::error(
                    $exception->getMessage(),
                    status: 429,
                ),
                $exception instanceof AuthenticationException => ResponseBuilder::error(
                    __('auth.unauthenticated'),
                    status: 401,
                ),
                $exception instanceof AuthorizationException => ResponseBuilder::error(
                    __('auth.forbidden'),
                    status: 403,
                ),
                $exception instanceof ModelNotFoundException,
                $exception instanceof NotFoundHttpException => ResponseBuilder::error(
                    __('errors.not_found'),
                    status: 404,
                ),
                $exception instanceof TooManyRequestsHttpException => ResponseBuilder::error(
                    __('errors.too_many_requests'),
                    status: 429,
                ),
                $exception instanceof HttpExceptionInterface => ResponseBuilder::error(
                    $exception->getMessage() ?: __('errors.server_error'),
                    status: $exception->getStatusCode(),
                ),
                default => ResponseBuilder::error(
                    config('app.debug') ? $exception->getMessage() : __('errors.server_error'),
                    status: 500,
                ),
            };
        });

        /*
         * An expired session should never surface a raw 419 page.
         */
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => __('errors.session_expired')]);
        });
    })
    ->create();
