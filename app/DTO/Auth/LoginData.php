<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\Http\Requests\Auth\LoginRequest;

/**
 * Validated credentials submitted to the authentication service.
 */
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: mb_strtolower((string) $request->validated('email')),
            password: (string) $request->validated('password'),
            remember: $request->boolean('remember'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
