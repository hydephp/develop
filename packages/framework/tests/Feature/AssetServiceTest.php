<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\AssetService
 *
 * @see \Hyde\Framework\Testing\Unit\AssetServiceUnitTest
 */
class AssetServiceTest extends TestCase
{
    public function testMediaLinkReturnsMediaPathWithCacheKey()
    {
        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('media/app.css?v='.md5_file(Hyde::path('_media/app.css')), $path);
    }

    public function testMediaLinkReturnsMediaPathWithoutCacheKeyIfCacheBustingIsDisabled()
    {
        config(['hyde.enable_cache_busting' => false]);
        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('media/app.css', $path);
    }

    public function testMediaLinkSupportsCustomMediaDirectories()
    {
        $this->directory('_assets');
        $this->file('_assets/app.css');
        Hyde::setMediaDirectory('_assets');

        $service = new AssetService();
        $this->assertIsString($path = $service->mediaLink('app.css'));
        $this->assertEquals('assets/app.css?v='.md5_file(Hyde::path('_assets/app.css')), $path);
    }
}
