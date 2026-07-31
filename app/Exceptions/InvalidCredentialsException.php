<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised when submitted credentials do not match any account.
 *
 * The message intentionally avoids revealing whether the email exists.
 */
final class InvalidCredentialsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('auth.failed'));
    }
}
