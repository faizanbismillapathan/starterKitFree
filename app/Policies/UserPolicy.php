<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Authorisation rules for user records.
 *
 * Permission driven rather than role driven (21 RBAC convention adopted by
 * 10_Security_Architecture.md §5).
 */
final class UserPolicy
{
    public function view(User $user, User $model): bool
    {
        return $user->is($model) || $user->can(Permission::DashboardViewSystemStatistics->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->is($model) && $user->can(Permission::ProfileUpdate->value);
    }

    public function viewLoginHistory(User $user, User $model): bool
    {
        return $user->is($model) && $user->can(Permission::LoginHistoryView->value);
    }

    public function manageSessions(User $user, User $model): bool
    {
        return $user->is($model) && $user->can(Permission::SessionRevoke->value);
    }
}
