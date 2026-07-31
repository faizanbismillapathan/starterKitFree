<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Data access contract for authentication history records.
 */
interface LoginHistoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): LoginHistory;

    /**
     * @return LengthAwarePaginator<int, LoginHistory>
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator;

    /**
     * @return Collection<int, LoginHistory>
     */
    public function recentForUser(User $user, int $limit): Collection;

    /**
     * @return Collection<int, LoginHistory>
     */
    public function recent(int $limit): Collection;

    public function markLoggedOut(?string $sessionId): void;

    public function countFailedSince(\DateTimeInterface $since): int;

    public function countSuccessfulSince(\DateTimeInterface $since): int;
}
