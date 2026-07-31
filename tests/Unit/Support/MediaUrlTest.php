<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\MediaUrl;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Local assets are served by the application, so their address must follow the
 * incoming request rather than the configured APP_URL. Without this an
 * application browsed at 127.0.0.1:8000 would emit localhost image sources.
 */
final class MediaUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.url', 'http://localhost');
        Config::set('filesystems.disks.public.driver', 'local');
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');
    }

    #[Test]
    public function it_builds_local_urls_from_the_current_request_host(): void
    {
        $this->app['request'] = \Illuminate\Http\Request::create(
            'http://127.0.0.1:8000/profile',
            'GET',
        );
        $this->refreshUrlGenerator();

        $this->assertSame(
            'http://127.0.0.1:8000/storage/media/avatar/photo.png',
            MediaUrl::resolve('public', 'media/avatar/photo.png'),
        );
    }

    #[Test]
    public function it_honours_a_custom_domain(): void
    {
        $this->app['request'] = \Illuminate\Http\Request::create(
            'https://app.example.com/profile',
            'GET',
        );
        $this->refreshUrlGenerator();

        $this->assertSame(
            'https://app.example.com/storage/media/avatar/photo.png',
            MediaUrl::resolve('public', 'media/avatar/photo.png'),
        );
    }

    #[Test]
    public function it_preserves_a_custom_public_path_prefix(): void
    {
        Config::set('filesystems.disks.public.url', 'http://localhost/uploads');

        $this->app['request'] = \Illuminate\Http\Request::create(
            'http://127.0.0.1:8000/profile',
            'GET',
        );
        $this->refreshUrlGenerator();

        $this->assertSame(
            'http://127.0.0.1:8000/uploads/media/avatar/photo.png',
            MediaUrl::resolve('public', 'media/avatar/photo.png'),
        );
    }

    #[Test]
    public function it_tolerates_a_leading_slash_on_the_path(): void
    {
        $this->app['request'] = \Illuminate\Http\Request::create(
            'http://127.0.0.1:8000/profile',
            'GET',
        );
        $this->refreshUrlGenerator();

        $this->assertSame(
            'http://127.0.0.1:8000/storage/media/avatar/photo.png',
            MediaUrl::resolve('public', '/media/avatar/photo.png'),
        );
    }

    /**
     * Remote drivers keep their own URL generation so the media system stays
     * storage independent (16_Media_System.md §5).
     */
    #[Test]
    public function it_delegates_remote_disks_to_the_filesystem(): void
    {
        Config::set('filesystems.disks.cdn', [
            'driver' => 'scoped',
            'url' => 'https://cdn.example.com',
        ]);

        Storage::shouldReceive('disk')
            ->once()
            ->with('cdn')
            ->andReturn($fake = \Mockery::mock());

        $fake->shouldReceive('url')
            ->once()
            ->with('media/avatar/photo.png')
            ->andReturn('https://cdn.example.com/media/avatar/photo.png');

        $this->assertSame(
            'https://cdn.example.com/media/avatar/photo.png',
            MediaUrl::resolve('cdn', 'media/avatar/photo.png'),
        );
    }

    /**
     * Rebinds the URL generator so it picks up the freshly created request.
     */
    private function refreshUrlGenerator(): void
    {
        $this->app->forgetInstance('url');
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('url');
        \Illuminate\Support\Facades\URL::clearResolvedInstances();
    }
}
