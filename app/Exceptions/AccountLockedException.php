<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised when an account is temporarily locked after repeated failed logins
 * (17_Authentication_Module.md §19).
 */
final class AccountLockedException extends RuntimeException
{
    public function __construct(public readonly int $secondsRemaining)
    {
        parent::__construct(__('auth.throttle', [
            'seconds' => $secondsRemaining,
            'minutes' => (int) ceil($secondsRemaining / 60),
        ]));
    }
}
