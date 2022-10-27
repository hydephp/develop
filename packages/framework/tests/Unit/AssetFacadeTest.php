<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Services\AssetService;
use Hyde\Helpers\Asset;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Helpers\Asset
 */
class AssetFacadeTest extends TestCase
{
    public function test_asset_facade_returns_the_asset_service()
    {
        $this->assertInstanceOf(AssetService::class, Asset::getFacadeRoot());
    }

    public function test_facade_returns_same_instance_as_bound_by_the_container()
    {
        $this->assertSame(Asset::getFacadeRoot(), app(AssetService::class));
    }

    public function test_asset_facade_can_call_methods_on_the_asset_service()
    {
        $service = new AssetService();
        $this->assertEquals($service->version(), Asset::version());
    }
}
