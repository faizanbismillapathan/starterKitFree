<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\Http\Requests\Auth\RegisterRequest;

/**
 * Validated payload for user registration.
 *
 * DTOs carry validated data between layers so request objects never leak past
 * the controller (06_System_Architecture.md §13).
 */
final readonly class RegisterUserData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            firstName: (string) $request->validated('first_name'),
            lastName: (string) $request->validated('last_name'),
            email: mb_strtolower((string) $request->validated('email')),
            password: (string) $request->validated('password'),
            phone: $request->validated('phone'),
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
        ];
    }
}
