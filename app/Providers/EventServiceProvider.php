<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\PasswordChanged;
use App\Events\UserRegistered;
use App\Listeners\FlushDashboardCache;
use App\Listeners\SendPasswordChangedNotification;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event to listener bindings.
 *
 * Events decouple side effects such as notifications and cache invalidation
 * from the primary business operation (06_System_Architecture.md §8).
 */
final class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeNotification::class,
            FlushDashboardCache::class,
        ],
        PasswordChanged::class => [
            SendPasswordChangedNotification::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
