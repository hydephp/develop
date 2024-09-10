<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Asset;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\CreatesApplication;
use Hyde\Framework\Services\AssetService;

/**
 * @covers \Hyde\Facades\Asset
 */
class AssetFacadeTest extends UnitTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->createApplication();
    }

    public function testAssetFacadeReturnsTheAssetService()
    {
        $this->assertInstanceOf(AssetService::class, Asset::getFacadeRoot());
    }

    public function testFacadeReturnsSameInstanceAsBoundByTheContainer()
    {
        $this->assertSame(Asset::getFacadeRoot(), app(AssetService::class));
    }

    public function testAssetFacadeCanCallMethodsOnTheAssetService()
    {
        $service = new AssetService();
        $this->assertEquals($service->version(), Asset::version());
    }
}
