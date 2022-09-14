<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\AssetService;
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

    public function test_media_link_returns_media_path()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('styles.css'));
        $this->assertEquals('media/styles.css', $path);
    }
}
