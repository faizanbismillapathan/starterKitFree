<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Outcome recorded for each authentication attempt.
 *
 * Supports the login history requirement in 17_Authentication_Module.md §23.
 */
enum LoginStatus: string
{
    case Successful = 'successful';
    case Failed = 'failed';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Successful => __('auth.login_status.successful'),
            self::Failed => __('auth.login_status.failed'),
            self::Locked => __('auth.login_status.locked'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Successful => 'success',
            self::Failed => 'danger',
            self::Locked => 'warning',
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
