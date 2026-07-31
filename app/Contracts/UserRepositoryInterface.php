<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Data access contract for users.
 *
 * Repositories expose persistence concerns only; business rules belong to the
 * service layer (06_System_Architecture.md §10).
 */
interface UserRepositoryInterface
{
    public function find(int $id): ?User;

    public function findOrFail(int $id): User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User;

    public function delete(User $user): bool;

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(int $perPage, ?string $search = null): LengthAwarePaginator;

    /**
     * @return Collection<int, User>
     */
    public function recent(int $limit): Collection;

    public function count(): int;

    public function countActive(): int;

    public function countVerified(): int;

    public function countCreatedSince(\DateTimeInterface $since): int;
}
