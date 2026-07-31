<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after a user password has been replaced.
 */
final class PasswordChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly User $user) {}
}
