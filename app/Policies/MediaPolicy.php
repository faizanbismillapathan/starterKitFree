<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Media;
use App\Models\User;

/**
 * Authorisation rules for stored assets.
 *
 * Ownership is verified in addition to the permission check so users cannot
 * reach another account's files (16_Media_System.md §25).
 */
final class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return $user->can(Permission::MediaView->value) && $this->owns($user, $media);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::MediaUpload->value);
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->can(Permission::MediaDelete->value) && $this->owns($user, $media);
    }

    private function owns(User $user, Media $media): bool
    {
        return $media->mediable_type === $user->getMorphClass()
            && (int) $media->mediable_id === (int) $user->getKey();
    }
}
