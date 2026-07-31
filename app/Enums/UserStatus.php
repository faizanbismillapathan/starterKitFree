<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle states a user account may occupy.
 *
 * Mirrors the authentication states documented in 17_Authentication_Module.md §13.
 * Only the states reachable in the Free Edition are represented here.
 */
enum UserStatus: string
{
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => __('users.status.pending_verification'),
            self::Active => __('users.status.active'),
            self::Inactive => __('users.status.inactive'),
            self::Suspended => __('users.status.suspended'),
        };
    }

    /**
     * Semantic colour token consumed by the status badge component.
     */
    public function color(): string
    {
        return match ($this) {
            self::PendingVerification => 'warning',
            self::Active => 'success',
            self::Inactive => 'neutral',
            self::Suspended => 'danger',
        };
    }

    /**
     * Determines whether an account in this state may establish a session.
     */
    public function canAuthenticate(): bool
    {
        return match ($this) {
            self::Active, self::PendingVerification => true,
            self::Inactive, self::Suspended => false,
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
