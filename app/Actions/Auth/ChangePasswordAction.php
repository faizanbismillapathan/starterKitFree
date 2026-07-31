<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Replaces a user password and optionally revokes existing sessions.
 */
final readonly class ChangePasswordAction
{
    public function execute(User $user, string $plainPassword): User
    {
        $user->forceFill([
            'password' => Hash::make($plainPassword),
            'remember_token' => null,
        ])->save();

        return $user->refresh();
    }
}
