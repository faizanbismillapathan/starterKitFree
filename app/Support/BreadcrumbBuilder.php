<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Fluent builder for page breadcrumbs.
 *
 * Every page except the dashboard displays a breadcrumb trail
 * (11_Layout_System.md §12).
 */
final class BreadcrumbBuilder
{
    /**
     * @var array<int, array{label: string, url: string|null}>
     */
    private array $crumbs = [];

    public static function make(): self
    {
        return new self;
    }

    /**
     * Starts a trail rooted at the dashboard.
     */
    public static function forDashboard(): self
    {
        return self::make()->add(__('navigation.dashboard'), route('dashboard'));
    }

    public function add(string $label, ?string $url = null): self
    {
        $this->crumbs[] = ['label' => $label, 'url' => $url];

        return $this;
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    public function toArray(): array
    {
        return $this->crumbs;
    }
}
