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

    public function testServiceHasVersionString()
    {
        $this->assertIsString((new AssetService())->version());
    }

    public function testCdnLinkHelper()
    {
        $this->assertSame(
            'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/styles.css',
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
