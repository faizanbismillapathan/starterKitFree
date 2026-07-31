<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\DashboardService;

/**
 * Keeps dashboard aggregates accurate after data changes.
 *
 * Cache invalidation is explicit and documented (06_System_Architecture.md §17).
 */
final readonly class FlushDashboardCache
{
    public function __construct(private DashboardService $dashboard) {}

    public function handle(object $event): void
    {
        $this->dashboard->flushCache();
    }
}
