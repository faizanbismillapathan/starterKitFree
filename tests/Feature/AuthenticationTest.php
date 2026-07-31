<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RoleType;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Covers the API authentication surface described in
 * 17_Authentication_Module.md: registration, sign-in for an active account and
 * refusal for an account whose status forbids access.
 */
final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Password satisfying the configured policy while remaining stable across
     * runs, so tests never depend on generated values.
     */
    private const PASSWORD = 'Str0ng!Passw0rd#2026';

    protected function setUp(): void
    {
        parent::setUp();

        // Roles must exist before registration can assign the default one.
        $this->seed(RolePermissionSeeder::class);

        // Registration dispatches a welcome notification; keep tests offline.
        Notification::fake();

        /*
         * The password policy calls the Have I Been Pwned service. Tests must
         * not depend on the network, so that single rule is relaxed here while
         * every other requirement stays in force.
         */
        config([
            'starter_kit.auth.password.min_length' => 10,
            'starter_kit.auth.password.require_uppercase' => true,
            'starter_kit.auth.password.require_lowercase' => true,
            'starter_kit.auth.password.require_numbers' => true,
            'starter_kit.auth.password.require_symbols' => true,
        ]);

        Password::$defaultCallback = null;
    }

    // ------------------------------------------------------------------
    // Registration
    // ------------------------------------------------------------------

    #[Test]
    public function a_visitor_can_register_through_the_api(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'first_name' => 'Meera',
            'last_name' => 'Nair',
            'email' => 'meera.nair@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'terms' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'meera.nair@example.com')
            ->assertJsonPath('data.user.full_name', 'Meera Nair');

        $this->assertDatabaseHas('users', [
            'email' => 'meera.nair@example.com',
            'first_name' => 'Meera',
        ]);

        $user = User::query()->where('email', 'meera.nair@example.com')->sole();

        // The configured default role is granted on creation.
        $this->assertTrue($user->hasRole(RoleType::Viewer->value));

        // Credentials are never persisted in plain text.
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));

        // The response must never leak the password hash.
        $response->assertJsonMissingPath('data.user.password');
    }

    #[Test]
    public function registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/register', [
            'first_name' => 'Sam',
            'last_name' => 'Kaur',
            'email' => 'taken@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'terms' => true,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('email');
    }

    // ------------------------------------------------------------------
    // Sign-in
    // ------------------------------------------------------------------

    #[Test]
    public function an_active_user_receives_a_token_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => self::PASSWORD,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'active@example.com',
            'password' => self::PASSWORD,
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user']]);

        $this->assertNotEmpty($response->json('data.token'));

        // A personal access token is issued and the attempt is audited.
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'phpunit',
        ]);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
            'status' => 'successful',
        ]);
    }

    #[Test]
    public function the_issued_token_grants_access_to_protected_endpoints(): void
    {
        $user = User::factory()->create([
            'email' => 'token@example.com',
            'password' => self::PASSWORD,
            'status' => UserStatus::Active,
        ]);
        $user->assignRole(RoleType::Viewer->value);

        $token = $this->postJson('/api/v1/login', [
            'email' => 'token@example.com',
            'password' => self::PASSWORD,
        ])->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'token@example.com');
    }

    #[Test]
    public function login_is_refused_for_an_inactive_user(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => self::PASSWORD,
            'status' => UserStatus::Suspended,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'suspended@example.com',
            'password' => self::PASSWORD,
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('auth.account_inactive'));

        // A refused sign-in must never mint a token.
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    #[Test]
    public function login_is_refused_for_an_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'active2@example.com',
            'password' => self::PASSWORD,
            'status' => UserStatus::Active,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'active2@example.com',
            'password' => 'WrongPassword!99',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('login_histories', [
            'email' => 'active2@example.com',
            'status' => 'failed',
        ]);
    }
}
