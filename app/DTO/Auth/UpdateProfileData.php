<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\Http\Requests\Auth\UpdateProfileRequest;

/**
 * Validated payload for profile updates.
 */
final readonly class UpdateProfileData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $phone = null,
        public ?string $timezone = null,
        public ?string $locale = null,
    ) {}

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        return new self(
            firstName: (string) $request->validated('first_name'),
            lastName: (string) $request->validated('last_name'),
            email: mb_strtolower((string) $request->validated('email')),
            phone: $request->validated('phone'),
            timezone: $request->validated('timezone'),
            locale: $request->validated('locale'),
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
            'phone' => $this->phone,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
        ];
    }
}
