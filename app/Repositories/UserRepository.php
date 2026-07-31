<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Enums\UserStatus;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the user data access contract.
 */
final class UserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findOrFail(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes)->save();

        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(int $perPage, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'media'])
            ->search($search)
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, User>
     */
    public function recent(int $limit): Collection
    {
        return User::query()
            ->with(['roles', 'media'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function count(): int
    {
        return User::query()->count();
    }

    public function countActive(): int
    {
        return User::query()->where('status', UserStatus::Active)->count();
    }

    public function countVerified(): int
    {
        return User::query()->whereNotNull('email_verified_at')->count();
    }

    public function countCreatedSince(DateTimeInterface $since): int
    {
        return User::query()->where('created_at', '>=', $since)->count();
    }
}
