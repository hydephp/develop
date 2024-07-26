<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Asset;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Asset
 *
 * @see \Hyde\Framework\Testing\Unit\Facades\AssetFacadeUnitTest
 */
class AssetFacadeTest extends TestCase
{
    public function testMediaLinkReturnsMediaPathWithCacheKey()
    {
        $this->assertIsString($path = Asset::mediaLink('app.css'));
        $this->assertSame('media/app.css?v='.md5_file(Hyde::path('_media/app.css')), $path);
    }

    public function testMediaLinkReturnsMediaPathWithoutCacheKeyIfCacheBustingIsDisabled()
    {
        config(['hyde.enable_cache_busting' => false]);

        $path = Asset::mediaLink('app.css');

        $this->assertIsString($path);
        $this->assertSame('media/app.css', $path);
    }

    public function testMediaLinkSupportsCustomMediaDirectories()
    {
        $this->directory('_assets');
        $this->file('_assets/app.css');

        Hyde::setMediaDirectory('_assets');

        $path = Asset::mediaLink('app.css');

        $this->assertIsString($path);
        $this->assertSame('assets/app.css?v='.md5_file(Hyde::path('_assets/app.css')), $path);
    }
}
