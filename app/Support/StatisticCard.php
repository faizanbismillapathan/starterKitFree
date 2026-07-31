<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Immutable value object backing the statistics card component.
 *
 * Cards expose a value, label, trend and icon (18_Dashboard_Module.md §8).
 */
final readonly class StatisticCard
{
    public function __construct(
        public string $key,
        public string $label,
        public string $value,
        public string $icon,
        public ?float $trend = null,
        public ?string $caption = null,
    ) {}

    public function hasTrend(): bool
    {
        return $this->trend !== null;
    }

    public function trendIsPositive(): bool
    {
        return ($this->trend ?? 0) >= 0;
    }

    /**
     * Signed, human readable representation of the trend.
     */
    public function formattedTrend(): ?string
    {
        if ($this->trend === null) {
            return null;
        }

        return sprintf('%+.1f%%', $this->trend);
    }
}
