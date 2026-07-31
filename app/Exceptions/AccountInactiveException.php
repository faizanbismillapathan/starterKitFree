<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\UserStatus;
use RuntimeException;

/**
 * Raised when an account exists but its status forbids authentication
 * (17_Authentication_Module.md §13).
 */
final class AccountInactiveException extends RuntimeException
{
    public function __construct(public readonly UserStatus $status)
    {
        parent::__construct(__('auth.account_inactive'));
    }
}
