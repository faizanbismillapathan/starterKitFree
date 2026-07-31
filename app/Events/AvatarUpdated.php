<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after a profile picture has been replaced or removed.
 */
final class AvatarUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ?Media $media = null,
    ) {}
}
