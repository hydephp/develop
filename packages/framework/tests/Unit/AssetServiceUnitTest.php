<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\AssetService;
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

    public function testVersionStringConstant()
    {
        $this->assertSame('v2.0', AssetService::HYDEFRONT_VERSION);
    }

    public function testServiceHasVersionString()
    {
        $service = new AssetService();
        $this->assertIsString($service->version);
    }

    public function testVersionStringDefaultsToConstant()
    {
        $service = new AssetService();
        $this->assertSame(AssetService::HYDEFRONT_VERSION, $service->version);
    }

    public function testCanChangeVersion()
    {
        $service = new AssetService();
        $service->version = '1.0.0';
        $this->assertEquals('1.0.0', $service->version);
    }

    public function testVersionCanBeSetInConfig()
    {
        self::mockConfig(['hyde.hydefront_version' => '1.0.0']);
        $service = new AssetService();
        $this->assertEquals('1.0.0', $service->version());
    }

    public function testCanSetCustomCdnUriInConfig()
    {
        self::mockConfig(['hyde.hydefront_url' => 'https://example.com']);
        $service = new AssetService();
        $this->assertSame('https://example.com', $service->cdnLink(''));
    }

    public function testVersionMethodReturnsVersionProperty()
    {
        $service = new AssetService();
        $this->assertSame($service->version, $service->version());
    }

    public function testCdnPathConstructorReturnsCdnUri()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->cdnLink('styles.css'));
        $this->assertStringContainsString('styles.css', $path);
    }

    public function testInjectTailwindConfigReturnsExtractedTailwindConfig()
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
