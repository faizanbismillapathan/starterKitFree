<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ThemeMode;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Cached hash so large seeds do not repeatedly pay the bcrypt cost.
     */
    private static ?string $passwordHash = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Str::lower($firstName.'.'.$lastName.fake()->unique()->numberBetween(1, 99_999).'@example.com'),
            'phone' => fake()->optional(0.6)->numerify('+1##########'),
            'password' => self::$passwordHash ??= Hash::make('Password!2345'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
            'timezone' => fake()->timezone(),
            'locale' => 'en',
            'theme' => fake()->randomElement(ThemeMode::cases()),
            'remember_token' => Str::random(10),
            'login_count' => fake()->numberBetween(0, 120),
            'last_login_at' => fake()->optional(0.8)->dateTimeBetween('-30 days'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
            'status' => UserStatus::PendingVerification,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => UserStatus::Suspended]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => UserStatus::Inactive]);
    }
}
