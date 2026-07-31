<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the login history data access contract.
 */
final class LoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): LoginHistory
    {
        return LoginHistory::query()->create($attributes);
    }

    /**
     * @return LengthAwarePaginator<int, LoginHistory>
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return LoginHistory::query()
            ->where('user_id', $user->getKey())
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, LoginHistory>
     */
    public function recentForUser(User $user, int $limit): Collection
    {
        return LoginHistory::query()
            ->where('user_id', $user->getKey())
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, LoginHistory>
     */
    public function recent(int $limit): Collection
    {
        return LoginHistory::query()
            ->with('user.media')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function markLoggedOut(?string $sessionId): void
    {
        if (blank($sessionId)) {
            return;
        }

        LoginHistory::query()
            ->where('session_id', $sessionId)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);
    }

    public function countFailedSince(DateTimeInterface $since): int
    {
        return LoginHistory::query()
            ->where('status', LoginStatus::Failed)
            ->where('created_at', '>=', $since)
            ->count();
    }

    public function countSuccessfulSince(DateTimeInterface $since): int
    {
        return LoginHistory::query()
            ->where('status', LoginStatus::Successful)
            ->where('created_at', '>=', $since)
            ->count();
    }
}
