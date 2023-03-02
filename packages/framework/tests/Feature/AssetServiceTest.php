<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\AssetService
 *
 * @see \Hyde\Framework\Testing\Unit\AssetServiceUnitTest
 */
class AssetServiceTest extends TestCase
{
    public function test_version_can_be_set_in_config()
    {
        config(['hyde.hydefront_version' => '1.0.0']);
        $service = new AssetService();
        $this->assertEquals('1.0.0', $service->version());
    }

    public function test_can_set_custom_cdn_uri_in_config()
    {
        config(['hyde.hydefront_url' => 'https://example.com']);
        $service = new AssetService();
        $this->assertSame('https://example.com', $service->cdnLink('styles.css'));
    }

    public function test_media_link_returns_media_path_with_cache_key()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('media/app.css?v='.md5_file(Hyde::path('_media/app.css')), $path);
    }

    public function test_media_link_returns_media_path_without_cache_key_if_cache_busting_is_disabled()
    {
        config(['hyde.cache_busting' => false]);
        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('media/app.css', $path);
    }

    public function test_media_link_supports_custom_media_directories()
    {
        $this->directory('_assets');
        $this->file('_assets/app.css');
        Hyde::setMediaDirectory('_assets');

        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('assets/app.css?v='.md5_file(Hyde::path('_assets/app.css')), $path);
    }
}
