<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\UserRepositoryInterface;
use App\DTO\Auth\RegisterUserData;
use App\Enums\UserStatus;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Creates a user account and assigns the configured default role.
 *
 * Actions perform exactly one business operation
 * (06_System_Architecture.md §12).
 */
final readonly class CreateUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(RegisterUserData $data): User
    {
        $requiresVerification = (bool) config('starter_kit.auth.email_verification_required');

        $user = $this->users->create([
            ...$data->toArray(),
            'status' => $requiresVerification
                ? UserStatus::PendingVerification
                : UserStatus::Active,
        ]);

        if (! $requiresVerification) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $this->assignDefaultRole($user);

        return $user->refresh();
    }

    /**
     * Assigns the default role when it has been seeded.
     *
     * The role is looked up rather than created so seeding remains the single
     * source of truth for the RBAC catalogue.
     */
    private function assignDefaultRole(User $user): void
    {
        $roleName = (string) config('starter_kit.auth.default_role');

        if ($roleName === '' || $user->hasRole($roleName)) {
            return;
        }

        $exists = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->exists();

        if ($exists) {
            $user->assignRole($roleName);
        }
    }
}
