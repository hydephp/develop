<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\AssetService
 */
class AssetServiceTest extends TestCase
{
    public function test_has_version_string()
    {
        $service = new AssetService();
        $this->assertIsString($service->version);
    }

    public function test_can_change_version()
    {
        $service = new AssetService();
        $service->version = '1.0.0';
        $this->assertEquals('1.0.0', $service->version);
    }

    public function test_version_method_returns_version_property_when_config_override_is_not_set()
    {
        $service = new AssetService();
        $this->assertEquals($service->version, $service->version());
    }

    public function test_cdn_path_constructor_returns_cdn_uri()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->constructCdnPath('styles.css'));
        $this->assertStringContainsString('styles.css', $path);
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

    public function test_inject_tailwind_config_returns_extracted_tailwind_config()
    {
        $service = new AssetService();
        $this->assertIsString($config = $service->injectTailwindConfig());
        $this->assertStringContainsString("darkMode: 'class'", $config);
        $this->assertStringContainsString('theme: {', $config);
        $this->assertStringContainsString('extend: {', $config);
        $this->assertStringContainsString('typography: {', $config);
    }
}
