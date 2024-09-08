<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Hyde;
use Hyde\Facades\Asset;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Testing\CreatesTemporaryFiles;
use Hyde\Framework\Exceptions\FileNotFoundException;

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
        $this->assertSame(Hyde::asset('app.css'), Asset::get('app.css'));
    }

    public function testGetHelperWithNonExistentFile()
    {
        $this->expectException(FileNotFoundException::class);
        Asset::get('styles.css');
    }

    public function testHasMediaFileHelper()
    {
        $this->assertFalse(Asset::exists('styles.css'));
    }

    public function testHasMediaFileHelperReturnsTrueForExistingFile()
    {
        $this->assertTrue(Asset::exists('app.css'));
    }

    public function testAssetReturnsMediaPathWithCacheKey()
    {
        $this->assertIsString($path = (string) Asset::get('app.css'));
        $this->assertSame('media/app.css?v='.hash_file('crc32', Hyde::path('_media/app.css')), $path);
    }

    public function testAssetReturnsMediaPathWithoutCacheKeyIfCacheBustingIsDisabled()
    {
        self::mockConfig(['hyde.enable_cache_busting' => false]);

        $path = (string) Asset::get('app.css');

        $this->assertIsString($path);
        $this->assertSame('media/app.css', $path);
    }

    public function testAssetSupportsCustomMediaDirectories()
    {
        self::resetKernel();

        Hyde::setMediaDirectory('_assets');

        $this->directory('_assets');
        $this->file('_assets/app.css');

        $path = (string) Asset::get('app.css');

        $this->assertIsString($path);
        $this->assertSame('assets/app.css?v='.hash_file('crc32', Hyde::path('_assets/app.css')), $path);
    }
}
