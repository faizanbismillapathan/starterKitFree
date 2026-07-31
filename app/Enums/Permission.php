<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Permissions registered by the Free Edition.
 *
 * Naming follows the documented `module.action` convention
 * (08_Module_Architecture.md §10).
 */
enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case DashboardCustomize = 'dashboard.customize';
    case DashboardViewSystemStatistics = 'dashboard.view_system_statistics';

    case ProfileView = 'profile.view';
    case ProfileUpdate = 'profile.update';

    case SessionView = 'sessions.view';
    case SessionRevoke = 'sessions.revoke';

    case LoginHistoryView = 'login_history.view';

    case MediaView = 'media.view';
    case MediaUpload = 'media.upload';
    case MediaDelete = 'media.delete';

    /**
     * The module a permission belongs to, used to group the permission matrix.
     */
    public function module(): string
    {
        return str($this->value)->before('.')->toString();
    }

    /**
     * The action portion of the permission name.
     */
    public function action(): string
    {
        return str($this->value)->after('.')->toString();
    }

    public function label(): string
    {
        return __('permissions.'.$this->value);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Permissions grouped by module for administrative screens.
     *
     * @return array<string, array<int, self>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $grouped[$case->module()][] = $case;
        }

        return $grouped;
    }
}
