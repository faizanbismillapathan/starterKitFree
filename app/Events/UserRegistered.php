<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched once a user account has been created.
 */
final class UserRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly User $user) {}
}
