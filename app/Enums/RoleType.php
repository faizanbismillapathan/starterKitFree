<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Roles shipped with the Free Edition.
 *
 * The RBAC model itself is provided by spatie/laravel-permission; this enum
 * removes magic strings when referencing the roles seeded by the application.
 */
enum RoleType: string
{
    case SuperAdministrator = 'Super Administrator';
    case Administrator = 'Administrator';
    case Manager = 'Manager';
    case Viewer = 'Viewer';

    /**
     * Roles protected from deletion or renaming.
     */
    public function isSystemRole(): bool
    {
        return $this === self::SuperAdministrator;
    }

    /**
     * Short description surfaced when presenting roles to administrators.
     */
    public function description(): string
    {
        return match ($this) {
            self::SuperAdministrator => __('roles.description.super_administrator'),
            self::Administrator => __('roles.description.administrator'),
            self::Manager => __('roles.description.manager'),
            self::Viewer => __('roles.description.viewer'),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
