<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised when an upload cannot be persisted to the configured disk.
 */
final class MediaUploadException extends RuntimeException
{
    public static function storageFailed(string $filename): self
    {
        return new self(__('media.errors.storage_failed', ['file' => $filename]));
    }

    public static function unsupportedType(string $mimeType): self
    {
        return new self(__('media.errors.unsupported_type', ['type' => $mimeType]));
    }
}
