<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Extracts human readable browser, platform and device labels from a raw
 * User-Agent header.
 *
 * Login history stores these values for auditing
 * (17_Authentication_Module.md §23). A small dependency-free parser is used
 * because the approved package list does not include a detection library.
 */
final class UserAgentParser
{
    /**
     * Ordered browser signatures. The first match wins, so more specific
     * engines are listed before the generic ones they masquerade as.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const BROWSERS = [
        ['/\bEdg(?:e|A|iOS)?\//i', 'Edge'],
        ['/\bOPR\//i', 'Opera'],
        ['/\bOpera\b/i', 'Opera'],
        ['/\bSamsungBrowser\//i', 'Samsung Internet'],
        ['/\bVivaldi\//i', 'Vivaldi'],
        ['/\bBrave\//i', 'Brave'],
        ['/\bFirefox\//i', 'Firefox'],
        ['/\bChrome\//i', 'Chrome'],
        ['/\bCriOS\//i', 'Chrome'],
        ['/\bSafari\//i', 'Safari'],
    ];

    /**
     * @var array<int, array{0: string, 1: string}>
     */
    private const PLATFORMS = [
        ['/\bWindows NT 10\.0\b/i', 'Windows 10/11'],
        ['/\bWindows NT\b/i', 'Windows'],
        ['/\biPhone\b/i', 'iOS'],
        ['/\biPad\b/i', 'iPadOS'],
        ['/\bAndroid\b/i', 'Android'],
        ['/\bMac OS X\b/i', 'macOS'],
        ['/\bCrOS\b/i', 'ChromeOS'],
        ['/\bUbuntu\b/i', 'Ubuntu'],
        ['/\bLinux\b/i', 'Linux'],
    ];

    /**
     * @return array{browser: string|null, platform: string|null, device: string|null}
     */
    public function parse(?string $userAgent): array
    {
        if (blank($userAgent)) {
            return ['browser' => null, 'platform' => null, 'device' => null];
        }

        return [
            'browser' => $this->match(self::BROWSERS, $userAgent),
            'platform' => $this->match(self::PLATFORMS, $userAgent),
            'device' => $this->device($userAgent),
        ];
    }

    /**
     * Produces a compact "Browser on Platform" summary for the interface.
     */
    public function describe(?string $userAgent): string
    {
        $parsed = $this->parse($userAgent);

        if ($parsed['browser'] === null && $parsed['platform'] === null) {
            return __('auth.sessions.unknown_device');
        }

        if ($parsed['platform'] === null) {
            return (string) $parsed['browser'];
        }

        if ($parsed['browser'] === null) {
            return (string) $parsed['platform'];
        }

        return __('auth.sessions.device_summary', [
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $signatures
     */
    private function match(array $signatures, string $userAgent): ?string
    {
        foreach ($signatures as [$pattern, $label]) {
            if (preg_match($pattern, $userAgent) === 1) {
                return $label;
            }
        }

        return null;
    }

    private function device(string $userAgent): string
    {
        if (preg_match('/\b(iPad|Tablet)\b/i', $userAgent) === 1) {
            return 'Tablet';
        }

        if (preg_match('/\b(Mobile|iPhone|Android)\b/i', $userAgent) === 1) {
            return 'Mobile';
        }

        return 'Desktop';
    }
}
