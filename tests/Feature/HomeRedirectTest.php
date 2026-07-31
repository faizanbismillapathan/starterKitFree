<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The application root forwards visitors to the dashboard, which in turn
 * redirects guests to the sign-in screen.
 */
final class HomeRedirectTest extends TestCase
{
    #[Test]
    public function the_root_redirects_to_the_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    #[Test]
    public function guests_reaching_the_dashboard_are_sent_to_sign_in(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    #[Test]
    public function the_sign_in_screen_is_reachable(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('auth.login.heading'));
    }

    #[Test]
    public function the_health_endpoint_reports_the_edition(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok');
    }
}
