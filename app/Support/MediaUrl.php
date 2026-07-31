<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves public addresses for stored files.
 *
 * Locally stored assets are served by the application itself, so their address
 * is derived from the current request rather than the configured `APP_URL`.
 * Without this the browser receives `http://localhost/...` while the page is
 * being served from `http://127.0.0.1:8000/...`, and the image fails to load.
 *
 * Remote drivers keep using the disk's own URL generator so the media system
 * remains storage independent (16_Media_System.md §5).
 */
final class MediaUrl
{
    /**
     * Public address for a path on the given disk.
     */
    public static function resolve(string $disk, string $path): string
    {
        $path = ltrim($path, '/');

        if (! self::isLocal($disk)) {
            return Storage::disk($disk)->url($path);
        }

        return asset(self::publicPrefix($disk).'/'.$path);
    }

    /**
     * Whether the disk is served from this application's public directory.
     */
    private static function isLocal(string $disk): bool
    {
        return config("filesystems.disks.{$disk}.driver") === 'local';
    }

    /**
     * Path segment the disk is exposed under, taken from its configured URL.
     *
     * Only the path is reused; the scheme and host come from the request.
     */
    private static function publicPrefix(string $disk): string
    {
        $configured = (string) config("filesystems.disks.{$disk}.url", '');

        if ($configured === '') {
            return 'storage';
        }

        $prefix = parse_url($configured, PHP_URL_PATH);

        return is_string($prefix) && trim($prefix, '/') !== ''
            ? trim($prefix, '/')
            : 'storage';
    }
}
