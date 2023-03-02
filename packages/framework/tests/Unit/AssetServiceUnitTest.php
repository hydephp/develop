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

    public function testHasVersionString()
    {
        $service = new AssetService();
        $this->assertIsString($service->version);
    }

    public function testCanChangeVersion()
    {
        $service = new AssetService();
        $service->version = '1.0.0';
        $this->assertEquals('1.0.0', $service->version);
    }

    public function testVersionMethodReturnsVersionPropertyWhenConfigOverrideIsNotSet()
    {
        $service = new AssetService();
        $this->assertEquals($service->version, $service->version());
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
