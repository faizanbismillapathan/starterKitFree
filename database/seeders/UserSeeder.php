<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Enums\UserStatus;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the default administrator plus realistic demonstration accounts.
 *
 * Seeders generate production-like data rather than placeholder values
 * (07A_Database_Standards.md §25).
 */
final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = $this->createAdministrator();

        if (app()->environment('production')) {
            return;
        }

        $this->createDemonstrationAccounts();
        $this->createLoginHistory($administrator);
    }

    private function createAdministrator(): User
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@example.com');

        $administrator = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'first_name' => (string) env('ADMIN_FIRST_NAME', 'Avery'),
                'last_name' => (string) env('ADMIN_LAST_NAME', 'Sinclair'),
                'password' => Hash::make((string) env('ADMIN_PASSWORD', 'Password!2345')),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
            ],
        );

        if (! $administrator->hasRole(RoleType::SuperAdministrator->value)) {
            $administrator->assignRole(RoleType::SuperAdministrator->value);
        }

        return $administrator;
    }

    private function createDemonstrationAccounts(): void
    {
        $manager = User::factory()->create([
            'first_name' => 'Priya',
            'last_name' => 'Raman',
            'email' => 'manager@example.com',
        ]);
        $manager->assignRole(RoleType::Manager->value);

        $viewer = User::factory()->create([
            'first_name' => 'Daniel',
            'last_name' => 'Okafor',
            'email' => 'viewer@example.com',
        ]);
        $viewer->assignRole(RoleType::Viewer->value);

        User::factory()
            ->count(24)
            ->create()
            ->each(fn (User $user) => $user->assignRole(RoleType::Viewer->value));

        User::factory()->count(4)->unverified()->create()
            ->each(fn (User $user) => $user->assignRole(RoleType::Viewer->value));
    }

    private function createLoginHistory(User $administrator): void
    {
        LoginHistory::factory()->count(12)->create([
            'user_id' => $administrator->getKey(),
            'email' => $administrator->email,
        ]);

        LoginHistory::factory()->count(5)->failed()->create([
            'user_id' => $administrator->getKey(),
            'email' => $administrator->email,
        ]);

        User::query()
            ->whereKeyNot($administrator->getKey())
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->each(function (User $user): void {
                LoginHistory::factory()
                    ->count(random_int(1, 4))
                    ->create([
                        'user_id' => $user->getKey(),
                        'email' => $user->email,
                    ]);
            });
    }
}
