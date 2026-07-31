<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after profile details have been saved.
 */
final class ProfileUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly User $user) {}
}
