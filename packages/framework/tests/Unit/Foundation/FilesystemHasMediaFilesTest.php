<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Foundation\Kernel\Filesystem
 */
class FilesystemHasMediaFilesTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected TestableFilesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new TestableFilesystem(Hyde::getInstance());
        MediaFile::$validateExistence = false;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        MediaFile::$validateExistence = true;
    }

    public function testAssetsMethodReturnsSameInstanceOnSubsequentCalls()
    {
        $firstCall = $this->filesystem->assets();
        $secondCall = $this->filesystem->assets();

        $this->assertSame($firstCall, $secondCall);
    }

    public function testAssetsMethodReturnsEmptyCollectionWhenNoMediaFiles()
    {
        $this->filesystem->setTestMediaFiles([]);

        $assets = $this->filesystem->assets();

        $this->assertInstanceOf(Collection::class, $assets);
        $this->assertTrue($assets->isEmpty());
    }

    public function testAssetsMethodWithNestedDirectories()
    {
        $this->filesystem->setTestMediaFiles([
            Hyde::path('_media/images/photo.jpg'),
            Hyde::path('_media/documents/report.pdf'),
        ]);

        $assets = $this->filesystem->assets();

        $this->assertCount(2, $assets);
        $this->assertTrue($assets->has('images/photo.jpg'));
        $this->assertTrue($assets->has('documents/report.pdf'));
    }

    public function testGetMediaGlobPatternWithCustomMediaDirectory()
    {
        Hyde::setMediaDirectory('custom_media');

        $pattern = $this->filesystem->getTestMediaGlobPattern();

        $this->assertStringContainsString('custom_media/', $pattern);

        Hyde::setMediaDirectory('_media');
    }

    public function testGetMediaGlobPatternWithCustomExtensions()
    {
        self::mockConfig(['hyde.media_extensions' => ['gif', 'svg']]);

        $pattern = $this->filesystem->getTestMediaGlobPattern();

        $this->assertStringContainsString('{gif,svg}', $pattern);
    }

    public function testDiscoverMediaFilesWithEmptyResult()
    {
        $this->filesystem->setTestMediaFiles([]);

        $result = $this->filesystem->getTestDiscoverMediaFiles();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function testDiscoverMediaFilesWithMultipleFiles()
    {
        $this->filesystem->setTestMediaFiles([
            Hyde::path('_media/image.jpg'),
            Hyde::path('_media/document.pdf'),
        ]);

        $result = $this->filesystem->getTestDiscoverMediaFiles();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(MediaFile::class, $result->get('image.jpg'));
        $this->assertInstanceOf(MediaFile::class, $result->get('document.pdf'));
    }
}

class TestableFilesystem extends Filesystem
{
    private static array $testMediaFiles = [];

    public function setTestMediaFiles(array $files): void
    {
        self::$testMediaFiles = $files;
    }

    protected static function getMediaFiles(): array
    {
        return self::$testMediaFiles;
    }

    public function getTestMediaGlobPattern(): string
    {
        return static::getMediaGlobPattern();
    }

    public function getTestDiscoverMediaFiles(): Collection
    {
        return static::discoverMediaFiles();
    }
}
