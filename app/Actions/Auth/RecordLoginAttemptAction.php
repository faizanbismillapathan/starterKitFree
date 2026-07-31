<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use App\Support\UserAgentParser;

/**
 * Persists a single authentication attempt to the login history.
 *
 * Logging must never interrupt the primary business operation, therefore the
 * caller treats this action as best-effort.
 */
final readonly class RecordLoginAttemptAction
{
    public function __construct(
        private LoginHistoryRepositoryInterface $histories,
        private UserAgentParser $parser,
    ) {}

    public function execute(
        LoginStatus $status,
        string $email,
        ?User $user = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $sessionId = null,
    ): LoginHistory {
        $agent = $this->parser->parse($userAgent);

        return $this->histories->create([
            'user_id' => $user?->getKey(),
            'email' => $email,
            'status' => $status,
            'ip_address' => $ipAddress,
            'browser' => $agent['browser'],
            'platform' => $agent['platform'],
            'device' => $agent['device'],
            'user_agent' => $userAgent,
            'session_id' => $sessionId,
            'logged_in_at' => $status === LoginStatus::Successful ? now() : null,
        ]);
    }
}
