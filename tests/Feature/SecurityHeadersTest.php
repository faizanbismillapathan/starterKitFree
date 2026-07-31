<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Guards the browser protections required by 10_Security_Architecture.md §16.
 *
 * These assertions exist so a future change cannot quietly drop a header or
 * reintroduce a static Content Security Policy nonce.
 */
final class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Headers that must accompany every response.
     *
     * @var array<string, string>
     */
    private const EXPECTED_HEADERS = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * These tests assert on headers and markup, not on compiled assets.
         * Stubbing Vite keeps them independent of `npm run build` having been
         * run first, while the tags it emits still carry the nonce.
         */
        $this->withoutVite();
    }

    // ------------------------------------------------------------------
    // Static headers
    // ------------------------------------------------------------------

    #[Test]
    public function web_responses_carry_the_security_headers(): void
    {
        $response = $this->get(route('login'))->assertOk();

        foreach (self::EXPECTED_HEADERS as $header => $value) {
            $response->assertHeader($header, $value);
        }

        $this->assertStringContainsString(
            'camera=()',
            (string) $response->headers->get('Permissions-Policy'),
        );
    }

    #[Test]
    public function api_responses_carry_the_security_headers(): void
    {
        $response = $this->getJson('/api/v1/health')->assertOk();

        foreach (self::EXPECTED_HEADERS as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    #[Test]
    public function only_one_content_security_policy_header_is_emitted(): void
    {
        $response = $this->get(route('login'))->assertOk();

        $this->assertCount(
            1,
            $response->headers->all('Content-Security-Policy'),
            'Exactly one Content-Security-Policy header must be sent.',
        );
    }

    // ------------------------------------------------------------------
    // Content Security Policy
    // ------------------------------------------------------------------

    #[Test]
    public function the_policy_locks_down_the_dangerous_directives(): void
    {
        $policy = (string) $this->get(route('login'))
            ->assertOk()
            ->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("base-uri 'self'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
    }

    #[Test]
    public function inline_scripts_are_allowed_only_through_a_nonce(): void
    {
        $response = $this->get(route('login'))->assertOk();

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression(
            "/script-src[^;]*'nonce-[A-Za-z0-9]+'/",
            $policy,
            'The script-src directive must carry a nonce.',
        );

        // A nonce is pointless if inline scripts are blanket-allowed.
        $scriptSrc = $this->directive($policy, 'script-src');
        $this->assertStringNotContainsString("'unsafe-inline'", $scriptSrc);
    }

    #[Test]
    public function every_inline_script_carries_the_header_nonce(): void
    {
        $response = $this->get(route('login'))->assertOk();

        $policy = (string) $response->headers->get('Content-Security-Policy');
        preg_match("/'nonce-([A-Za-z0-9]+)'/", $policy, $matches);

        $nonce = $matches[1] ?? null;
        $this->assertNotNull($nonce, 'The policy must publish a nonce.');

        $html = $response->getContent();

        preg_match_all('/<script\b(?![^>]*\bnonce=)[^>]*>/i', (string) $html, $unprotected);

        $this->assertSame(
            [],
            $unprotected[0],
            'Every script tag must carry the request nonce.',
        );

        $this->assertStringContainsString('nonce="'.$nonce.'"', (string) $html);
    }

    #[Test]
    public function the_nonce_is_regenerated_for_every_request(): void
    {
        $first = $this->extractNonce();
        $second = $this->extractNonce();

        $this->assertNotSame(
            $first,
            $second,
            'A reused nonce would let an attacker replay an inline script.',
        );
    }

    // ------------------------------------------------------------------
    // Transport security
    // ------------------------------------------------------------------

    #[Test]
    public function hsts_is_absent_on_plain_http(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    #[Test]
    public function hsts_is_applied_to_secure_requests(): void
    {
        $response = $this->get('https://localhost/login')->assertOk();

        $this->assertStringContainsString(
            'max-age=31536000',
            (string) $response->headers->get('Strict-Transport-Security'),
        );
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function extractNonce(): string
    {
        $policy = (string) $this->get(route('login'))
            ->assertOk()
            ->headers->get('Content-Security-Policy');

        preg_match("/'nonce-([A-Za-z0-9]+)'/", $policy, $matches);

        return $matches[1] ?? '';
    }

    /**
     * Returns a single directive from a policy string.
     */
    private function directive(string $policy, string $name): string
    {
        foreach (explode(';', $policy) as $directive) {
            $directive = trim($directive);

            if (str_starts_with($directive, $name.' ')) {
                return $directive;
            }
        }

        return '';
    }
}
