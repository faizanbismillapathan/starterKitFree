<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Auth\ChangePasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\Contracts\UserRepositoryInterface;
use App\DTO\Auth\UpdateProfileData;
use App\Enums\ThemeMode;
use App\Events\AvatarUpdated;
use App\Events\PasswordChanged;
use App\Events\ProfileUpdated;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Business rules for self-service account management.
 */
final readonly class ProfileService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private MediaService $mediaService,
        private UpdateProfileAction $updateProfile,
        private ChangePasswordAction $changePassword,
    ) {}

    public function update(User $user, UpdateProfileData $data): User
    {
        $user = DB::transaction(fn (): User => $this->updateProfile->execute($user, $data));

        event(new ProfileUpdated($user));

        return $user;
    }

    /**
     * Replaces the password and revokes other sessions when configured.
     */
    public function changePassword(User $user, string $plainPassword): User
    {
        $user = DB::transaction(fn (): User => $this->changePassword->execute($user, $plainPassword));

        event(new PasswordChanged($user));

        return $user;
    }

    /**
     * Stores a new avatar through the centralised media system.
     */
    public function updateAvatar(User $user, UploadedFile $file): Media
    {
        $collection = (string) config('media.avatar.collection');

        $media = DB::transaction(
            fn (): Media => $this->mediaService->replaceCollection($user, $file, $collection),
        );

        $this->users->update($user, ['avatar_path' => $media->path()]);

        event(new AvatarUpdated($user, $media));

        return $media;
    }

    public function removeAvatar(User $user): void
    {
        $collection = (string) config('media.avatar.collection');

        DB::transaction(function () use ($user, $collection): void {
            $this->mediaService->deleteCollection($user, $collection);
            $this->users->update($user, ['avatar_path' => null]);
        });

        event(new AvatarUpdated($user));
    }

    /**
     * Persists the interface preference used by the theme switcher.
     */
    public function updateTheme(User $user, ThemeMode $mode): User
    {
        return $this->users->update($user, ['theme' => $mode->value]);
    }
}
