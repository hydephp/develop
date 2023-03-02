<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Services\AssetService
 *
 * @see \Hyde\Framework\Testing\Feature\AssetServiceTest
 */
class AssetServiceUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

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
        $this->assertIsString($path = $service->cdnLink('styles.css'));
        $this->assertStringContainsString('styles.css', $path);
    }

    public function test_inject_tailwind_config_returns_extracted_tailwind_config()
    {
        $service = new AssetService();
        $this->assertIsString($config = $service->injectTailwindConfig());
        $this->assertStringContainsString("darkMode: 'class'", $config);
        $this->assertStringContainsString('theme: {', $config);
        $this->assertStringContainsString('extend: {', $config);
        $this->assertStringContainsString('typography: {', $config);
        $this->assertStringNotContainsString('plugins', $config);
    }
}
