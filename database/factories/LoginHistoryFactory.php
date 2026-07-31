<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginHistory>
 */
final class LoginHistoryFactory extends Factory
{
    protected $model = LoginHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loggedInAt = fake()->dateTimeBetween('-30 days');

        return [
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'status' => LoginStatus::Successful,
            'ip_address' => fake()->ipv4(),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'platform' => fake()->randomElement(['Windows 10/11', 'macOS', 'Linux', 'iOS', 'Android']),
            'device' => fake()->randomElement(['Desktop', 'Mobile', 'Tablet']),
            'user_agent' => fake()->userAgent(),
            'session_id' => fake()->sha1(),
            'logged_in_at' => $loggedInAt,
            'created_at' => $loggedInAt,
            'updated_at' => $loggedInAt,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => LoginStatus::Failed,
            'logged_in_at' => null,
            'session_id' => null,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'status' => LoginStatus::Locked,
            'logged_in_at' => null,
        ]);
    }
}
