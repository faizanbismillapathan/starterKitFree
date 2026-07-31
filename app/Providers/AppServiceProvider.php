<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\Contracts\MediaRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\Media;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Policies\UserPolicy;
use App\Repositories\LoginHistoryRepository;
use App\Repositories\MediaRepository;
use App\Repositories\UserRepository;
use App\Rules\StrongPassword;
use App\Support\MenuBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Contract to implementation bindings (06_System_Architecture.md §6).
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        LoginHistoryRepositoryInterface::class => LoginHistoryRepository::class,
        MediaRepositoryInterface::class => MediaRepository::class,
    ];

    /**
     * @var array<class-string, class-string>
     */
    private const POLICIES = [
        User::class => UserPolicy::class,
        Media::class => MediaPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(MenuBuilder::class);
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureUrls();
        $this->configurePasswordDefaults();
        $this->registerPolicies();
        $this->registerMorphMap();
        $this->registerBladeDirectives();
        $this->shareNavigation();
    }

    /**
     * Exposes the per-request Content Security Policy nonce to Blade.
     *
     * Inline scripts must carry the nonce so the policy can stay free of
     * `unsafe-inline` (10_Security_Architecture.md §16).
     */
    private function registerBladeDirectives(): void
    {
        Blade::directive('cspNonce', static fn (): string => '<?php echo e(csp_nonce()); ?>');
    }

    /**
     * Strict models surface missing eager loads during development rather than
     * silently issuing N+1 queries (38_Performance_Guide.md §7).
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);
    }

    private function configureUrls(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    private function configurePasswordDefaults(): void
    {
        Password::defaults(fn (): Password => StrongPassword::rules());
    }

    private function registerPolicies(): void
    {
        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Stable morph aliases keep polymorphic media rows readable and decoupled
     * from PHP namespaces.
     */
    private function registerMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'media' => Media::class,
        ]);
    }

    /**
     * Navigation is resolved lazily so it never runs for API requests.
     */
    private function shareNavigation(): void
    {
        View::composer('components.layout.sidebar', function ($view): void {
            $view->with('menuGroups', app(MenuBuilder::class)->sidebar());
        });
    }
}
