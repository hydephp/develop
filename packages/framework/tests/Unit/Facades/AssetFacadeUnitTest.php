<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Hyde;
use Hyde\Facades\Asset;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Testing\CreatesTemporaryFiles;

/**
 * @covers \Hyde\Facades\Asset
 */
class AssetFacadeUnitTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected function setUp(): void
    {
        self::needsKernel();
        self::mockConfig();

        Render::swap(new RenderData());
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }

    public function testGetHelper()
    {
        $this->assertSame(Hyde::asset('foo'), Asset::get('foo'));
    }

    public function testHasMediaFileHelper()
    {
        $this->assertFalse(Asset::hasMediaFile('styles.css'));
    }

    public function testHasMediaFileHelperReturnsTrueForExistingFile()
    {
        $this->assertTrue(Asset::hasMediaFile('app.css'));
    }

    public function testMediaLinkReturnsMediaPathWithCacheKey()
    {
        $this->assertIsString($path = Asset::mediaLink('app.css'));
        $this->assertSame('media/app.css?v='.hash_file('crc32', Hyde::path('_media/app.css')), $path);
    }

    public function testMediaLinkReturnsMediaPathWithoutCacheKeyIfCacheBustingIsDisabled()
    {
        self::mockConfig(['hyde.enable_cache_busting' => false]);

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
        $this->assertSame('assets/app.css?v='.hash_file('crc32', Hyde::path('_assets/app.css')), $path);
    }
}
