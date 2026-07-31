<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Theme preferences supported by the design system (05_Design_System.md §10).
 */
enum ThemeMode: string
{
    case Light = 'light';
    case Dark = 'dark';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Light => __('theme.light'),
            self::Dark => __('theme.dark'),
            self::System => __('theme.system'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Light => 'sun',
            self::Dark => 'moon',
            self::System => 'computer-desktop',
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
