<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\UserRepositoryInterface;
use App\DTO\Auth\UpdateProfileData;
use App\Models\User;

/**
 * Applies validated profile changes.
 *
 * Changing the email address resets verification so ownership is re-confirmed
 * (19_Profile_Module.md §17 workflow, applied here for the account owner).
 */
final readonly class UpdateProfileAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(User $user, UpdateProfileData $data): User
    {
        $emailChanged = $user->email !== $data->email;

        $user = $this->users->update($user, $data->toArray());

        if ($emailChanged && config('starter_kit.auth.email_verification_required')) {
            $user->forceFill(['email_verified_at' => null])->save();
            $user->sendEmailVerificationNotification();
        }

        return $user->refresh();
    }
}
