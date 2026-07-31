<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Request;

/**
 * A single navigation entry produced by {@see MenuBuilder}.
 *
 * Items are permission aware so unauthorised links are never rendered
 * (11_Layout_System.md §7).
 */
final class MenuItem
{
    /**
     * @param  array<int, self>  $children
     */
    public function __construct(
        public readonly string $label,
        public readonly ?string $route = null,
        public readonly ?string $icon = null,
        public readonly ?string $permission = null,
        public readonly array $children = [],
        public readonly ?string $badge = null,
        public readonly ?string $activePattern = null,
    ) {}

    public function url(): ?string
    {
        if ($this->route === null) {
            return null;
        }

        return route($this->route);
    }

    /**
     * Whether the current request resolves to this item or one of its children.
     */
    public function isActive(): bool
    {
        if ($this->activePattern !== null && Request::routeIs($this->activePattern)) {
            return true;
        }

        if ($this->route !== null && Request::routeIs($this->route)) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }
}
