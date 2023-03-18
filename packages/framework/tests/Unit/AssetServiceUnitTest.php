<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\AssetService;
use Hyde\Testing\UnitTestCase;
use Hyde\Hyde;

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
        $this->assertSame('v3.3', AssetService::HYDEFRONT_VERSION);
    }

    public function testServiceHasVersionString()
    {
        $this->assertIsString((new AssetService())->version());
    }

    public function testVersionStringDefaultsToConstant()
    {
        $this->assertSame(AssetService::HYDEFRONT_VERSION, (new AssetService())->version());
    }

    public function testVersionCanBeSetInConfig()
    {
        self::mockConfig(['hyde.hydefront_version' => '1.0.0']);
        $this->assertSame('1.0.0', (new AssetService())->version());
    }

    public function testCdnPatternConstant()
    {
        $this->assertSame('https://cdn.jsdelivr.net/npm/hydefront@{{ $version }}/dist/{{ $file }}', AssetService::HYDEFRONT_CDN_URL);
    }

    public function testCanSetCustomCdnUrlInConfig()
    {
        self::mockConfig(['hyde.hydefront_url' => 'https://example.com']);
        $this->assertSame('https://example.com', (new AssetService())->cdnLink(''));
    }

    public function testCanUseCustomCdnUrlWithVersion()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $version }}']);
        $this->assertSame('v3.3', (new AssetService())->cdnLink(''));
    }

    public function testCanUseCustomCdnUrlWithFile()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $file }}']);
        $this->assertSame('styles.css', (new AssetService())->cdnLink('styles.css'));
    }

    public function testCanUseCustomCdnUrlWithVersionAndFile()
    {
        self::mockConfig(['hyde.hydefront_url' => '{{ $version }}/{{ $file }}']);
        $this->assertSame('v3.3/styles.css', (new AssetService())->cdnLink('styles.css'));
    }

    public function testCanUseCustomCdnUrlWithCustomVersion()
    {
        self::mockConfig([
            'hyde.hydefront_url' => '{{ $version }}',
            'hyde.hydefront_version' => '1.0.0',
        ]);
        $this->assertSame('1.0.0', (new AssetService())->cdnLink(''));
    }

    public function testCdnLinkHelper()
    {
        $this->assertSame(
            'https://cdn.jsdelivr.net/npm/hydefront@v3.3/dist/styles.css',
            (new AssetService())->cdnLink('styles.css')
        );
    }

    public function testHasMediaFileHelper()
    {
        $this->assertFalse((new AssetService())->hasMediaFile('styles.css'));
    }

    public function testHasMediaFileHelperReturnsTrueForExistingFile()
    {
        $this->assertTrue((new AssetService())->hasMediaFile('app.css'));
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

    public function testInjectTailwindConfigHandlesMissingConfigFileGracefully()
    {
        rename(Hyde::path('tailwind.config.js'), Hyde::path('tailwind.config.js.bak'));
        $this->assertIsString((new AssetService())->injectTailwindConfig());
        $this->assertSame('', (new AssetService())->injectTailwindConfig());
        rename(Hyde::path('tailwind.config.js.bak'), Hyde::path('tailwind.config.js'));
    }
}
