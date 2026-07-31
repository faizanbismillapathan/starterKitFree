<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Inspects and revokes the database-backed sessions belonging to a user
 * (17_Authentication_Module.md §22).
 */
final readonly class SessionService
{
    public function __construct(private UserAgentParser $parser) {}

    /**
     * Sessions currently associated with the user, most recent first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forUser(User $user, string $currentSessionId): Collection
    {
        if (! $this->usesDatabaseDriver()) {
            /** @var Collection<int, array<string, mixed>> $empty */
            $empty = collect();

            return $empty;
        }

        return DB::table($this->table())
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (object $session): array => [
                'id' => (string) $session->id,
                'ip_address' => $session->ip_address,
                'device' => $this->parser->describe($session->user_agent),
                'details' => $this->parser->parse($session->user_agent),
                'last_active' => Carbon::createFromTimestamp((int) $session->last_activity),
                'is_current' => $session->id === $currentSessionId,
            ]);
    }

    /**
     * Revokes a single session. The active session is never removed here so
     * the user is not signed out unexpectedly.
     */
    public function revoke(User $user, string $sessionId, string $currentSessionId): bool
    {
        if (! $this->usesDatabaseDriver() || $sessionId === $currentSessionId) {
            return false;
        }

        return DB::table($this->table())
            ->where('user_id', $user->getKey())
            ->where('id', $sessionId)
            ->delete() > 0;
    }

    public function countForUser(User $user): int
    {
        if (! $this->usesDatabaseDriver()) {
            return 0;
        }

        return DB::table($this->table())
            ->where('user_id', $user->getKey())
            ->count();
    }

    private function usesDatabaseDriver(): bool
    {
        return config('session.driver') === 'database';
    }

    private function table(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
