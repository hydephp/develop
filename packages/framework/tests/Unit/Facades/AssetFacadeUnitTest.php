<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Hyde;
use Hyde\Facades\Asset;
use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Services\AssetService;

/**
 * @covers \Hyde\Facades\Asset
 *
 * @see \Hyde\Framework\Testing\Feature\AssetFacadeTest
 */
class AssetFacadeUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();

        // Temporary interoperability with the old AssetService
        Asset::swap(new AssetService());
    }

    public function testServiceHasVersionString()
    {
        $this->assertIsString(Asset::version());
    }

    public function testCdnLinkHelper()
    {
        $this->assertSame(
            'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/styles.css',
            Asset::cdnLink('styles.css')
        );
    }

    public function testHasMediaFileHelper()
    {
        $this->assertFalse(Asset::hasMediaFile('styles.css'));
    }

    public function testHasMediaFileHelperReturnsTrueForExistingFile()
    {
        $this->assertTrue(Asset::hasMediaFile('app.css'));
    }

    public function testInjectTailwindConfigReturnsExtractedTailwindConfig()
    {
        $this->assertIsString($config = Asset::injectTailwindConfig());
        $this->assertStringContainsString("darkMode: 'class'", $config);
        $this->assertStringContainsString('theme: {', $config);
        $this->assertStringContainsString('extend: {', $config);
        $this->assertStringContainsString('typography: {', $config);
        $this->assertStringNotContainsString('plugins', $config);
    }

    public function testInjectTailwindConfigHandlesMissingConfigFileGracefully()
    {
        rename(Hyde::path('tailwind.config.js'), Hyde::path('tailwind.config.js.bak'));
        $this->assertIsString(Asset::injectTailwindConfig());
        $this->assertSame('', Asset::injectTailwindConfig());
        rename(Hyde::path('tailwind.config.js.bak'), Hyde::path('tailwind.config.js'));
    }
}
