<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Asset;
use Hyde\Framework\Services\AssetService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Asset
 */
class AssetFacadeTest extends TestCase
{
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
