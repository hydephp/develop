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
        $this->assertSame('1.0.0', $service->version);
    }

    public function testVersionCanBeSetInConfig()
    {
        self::mockConfig(['hyde.hydefront_version' => '1.0.0']);
        $service = new AssetService();
        $this->assertSame('1.0.0', $service->version());
    }

    public function testCdnPatternConstant()
    {
        $this->assertSame('https://cdn.jsdelivr.net/npm/hydefront@{{ $version }}/dist/{{ $file }}', AssetService::HYDEFRONT_CDN_URL);
    }

    public function testCanSetCustomCdnUrlInConfig()
    {
        self::mockConfig(['hyde.hydefront_url' => 'https://example.com']);
        $service = new AssetService();
        $this->assertSame('https://example.com', $service->cdnLink(''));
    }

    public function testCanUseCustomCdnUrlWithVersion()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $version }}']);
        $service = new AssetService();
        $this->assertSame('v2.0', $service->cdnLink(''));
    }

    public function testCanUseCustomCdnUrlWithFile()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $file }}']);
        $service = new AssetService();
        $this->assertSame('styles.css', $service->cdnLink('styles.css'));
    }

    public function testCanUseCustomCdnUrlWithVersionAndFile()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $version }}/{{ $file }}']);
        $service = new AssetService();
        $this->assertSame('v2.0/styles.css', $service->cdnLink('styles.css'));
    }

    public function testCanUseCustomCdnUrlWithCustomVersion()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $version }}']);
        $service = new AssetService();
        $service->version = '1.0.0';
        $this->assertSame('1.0.0', $service->cdnLink(''));
    }

    public function testVersionMethodReturnsVersionProperty()
    {
        $service = new AssetService();
        $this->assertSame($service->version, $service->version());
    }

    public function testCdnLinkHelper()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->cdnLink('styles.css'));
        $this->assertSame('https://cdn.jsdelivr.net/npm/hydefront@v2.0/dist/styles.css', $path);
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
