<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Hyde;
use Hyde\Facades\HydeFront;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\HydeFront
 */
class HydeFrontFacadeTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    // Todo: Check the version is correct? (When running in monorepo)

    public function testVersionReturnsString()
    {
        $this->assertIsString(HydeFront::version());
    }

    public function testCdnLinkReturnsCorrectUrl()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/styles.css';
        $this->assertSame($expected, HydeFront::cdnLink('styles.css'));
    }

    public function testCdnLinkReturnsCorrectUrlForHydeCss()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/hyde.css';
        $this->assertSame($expected, HydeFront::cdnLink('hyde.css'));
    }

    public function testCdnLinkReturnsCorrectUrlForInvalidFile()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/invalid';
        $this->assertSame($expected, HydeFront::cdnLink('invalid'));
    }

    public function testInjectTailwindConfigReturnsExtractedTailwindConfig()
    {
        $config = HydeFront::injectTailwindConfig();

        $this->assertIsString($config);
        $this->assertStringContainsString("darkMode: 'class'", $config);
        $this->assertStringContainsString('theme: {', $config);
        $this->assertStringContainsString('extend: {', $config);
        $this->assertStringContainsString('typography: {', $config);
        $this->assertStringNotContainsString('plugins', $config);
    }

    public function testInjectTailwindConfigHandlesMissingConfigFileGracefully()
    {
        rename(Hyde::path('tailwind.config.js'), Hyde::path('tailwind.config.js.bak'));
        $this->assertIsString(HydeFront::injectTailwindConfig());
        $this->assertSame('', HydeFront::injectTailwindConfig());
        rename(Hyde::path('tailwind.config.js.bak'), Hyde::path('tailwind.config.js'));
    }
}
