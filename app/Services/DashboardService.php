<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\Contracts\MediaRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Enums\Permission;
use App\Models\User;
use App\Support\StatisticCard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

/**
 * Aggregates the data presented by the Dashboard module.
 *
 * Expensive aggregates are cached (18_Dashboard_Module.md §23) and every
 * widget respects the viewer's permissions (§27).
 */
final readonly class DashboardService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private LoginHistoryRepositoryInterface $histories,
        private MediaRepositoryInterface $media,
    ) {}

    /**
     * Key performance indicators rendered as statistic cards.
     *
     * @return array<int, StatisticCard>
     */
    public function statistics(User $viewer): array
    {
        if (! $viewer->can(Permission::DashboardView->value)) {
            return [];
        }

        $metrics = $this->cachedMetrics();

        $cards = [
            new StatisticCard(
                key: 'total_users',
                label: __('dashboard.statistics.total_users'),
                value: Number::format($metrics['total_users']),
                icon: 'users',
                trend: $metrics['users_trend'],
                caption: __('dashboard.statistics.compared_to_previous_period'),
            ),
            new StatisticCard(
                key: 'active_users',
                label: __('dashboard.statistics.active_users'),
                value: Number::format($metrics['active_users']),
                icon: 'user-check',
                caption: __('dashboard.statistics.active_caption'),
            ),
            new StatisticCard(
                key: 'verified_users',
                label: __('dashboard.statistics.verified_users'),
                value: Number::format($metrics['verified_users']),
                icon: 'badge-check',
                caption: __('dashboard.statistics.verified_caption'),
            ),
            new StatisticCard(
                key: 'successful_logins',
                label: __('dashboard.statistics.successful_logins'),
                value: Number::format($metrics['successful_logins']),
                icon: 'login',
                caption: __('dashboard.statistics.last_seven_days'),
            ),
        ];

        if (! $viewer->can(Permission::DashboardViewSystemStatistics->value)) {
            return array_slice($cards, 0, 2);
        }

        return $cards;
    }

    /**
     * Sign-up volume for the trailing fourteen days.
     *
     * @return array{categories: array<int, string>, series: array<int, int>}
     */
    public function registrationTrend(int $days = 14): array
    {
        return Cache::remember(
            'dashboard.registration_trend.'.$days,
            (int) config('starter_kit.dashboard.cache_ttl'),
            function () use ($days): array {
                $categories = [];
                $series = [];

                for ($offset = $days - 1; $offset >= 0; $offset--) {
                    $day = now()->subDays($offset)->startOfDay();

                    $categories[] = $day->format('M j');
                    $series[] = $this->users->countCreatedSince($day)
                        - $this->users->countCreatedSince($day->copy()->addDay());
                }

                return ['categories' => $categories, 'series' => $series];
            },
        );
    }

    /**
     * Authentication activity split by outcome for the trailing week.
     *
     * @return array{successful: int, failed: int}
     */
    public function authenticationSummary(): array
    {
        return Cache::remember(
            'dashboard.authentication_summary',
            (int) config('starter_kit.dashboard.cache_ttl'),
            fn (): array => [
                'successful' => $this->histories->countSuccessfulSince(now()->subDays(7)),
                'failed' => $this->histories->countFailedSince(now()->subDays(7)),
            ],
        );
    }

    /**
     * @return Collection<int, \App\Models\LoginHistory>
     */
    public function recentActivity(User $viewer): Collection
    {
        $limit = (int) config('starter_kit.dashboard.recent_activity_limit');

        if ($viewer->can(Permission::DashboardViewSystemStatistics->value)) {
            return $this->histories->recent($limit);
        }

        return $this->histories->recentForUser($viewer, $limit);
    }

    /**
     * @return Collection<int, User>
     */
    public function recentUsers(User $viewer): Collection
    {
        if (! $viewer->can(Permission::DashboardViewSystemStatistics->value)) {
            /** @var Collection<int, User> $empty */
            $empty = new Collection;

            return $empty;
        }

        return $this->users->recent((int) config('starter_kit.dashboard.recent_users_limit'));
    }

    /**
     * Operational status widget shown to privileged users.
     *
     * @return array<string, string>
     */
    public function systemStatus(User $viewer): array
    {
        if (! $viewer->can(Permission::DashboardViewSystemStatistics->value)) {
            return [];
        }

        return Cache::remember(
            'dashboard.system_status',
            (int) config('starter_kit.dashboard.cache_ttl'),
            fn (): array => [
                __('dashboard.system.edition') => (string) config('starter_kit.edition'),
                __('dashboard.system.version') => (string) config('starter_kit.version'),
                __('dashboard.system.environment') => (string) config('app.env'),
                __('dashboard.system.stored_media') => Number::format($this->media->count()),
            ],
        );
    }

    /**
     * Forgets every cached dashboard aggregate.
     */
    public function flushCache(): void
    {
        Cache::forget('dashboard.metrics');
        Cache::forget('dashboard.authentication_summary');
        Cache::forget('dashboard.system_status');

        foreach ([7, 14, 30] as $window) {
            Cache::forget('dashboard.registration_trend.'.$window);
        }
    }

    /**
     * @return array<string, int|float>
     */
    private function cachedMetrics(): array
    {
        return Cache::remember(
            'dashboard.metrics',
            (int) config('starter_kit.dashboard.cache_ttl'),
            function (): array {
                $currentPeriod = $this->users->countCreatedSince(now()->subDays(30));
                $previousPeriod = $this->users->countCreatedSince(now()->subDays(60))
                    - $currentPeriod;

                return [
                    'total_users' => $this->users->count(),
                    'active_users' => $this->users->countActive(),
                    'verified_users' => $this->users->countVerified(),
                    'successful_logins' => $this->histories->countSuccessfulSince(now()->subDays(7)),
                    'users_trend' => $this->percentageChange($previousPeriod, $currentPeriod),
                ];
            },
        );
    }

    /**
     * Percentage difference between two periods, rounded to one decimal.
     */
    private function percentageChange(int $previous, int $current): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
