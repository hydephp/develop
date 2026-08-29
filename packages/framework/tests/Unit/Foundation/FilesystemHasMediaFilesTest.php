<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Framework\Actions\Internal\FileFinder;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(Filesystem::class)]
class FilesystemHasMediaFilesTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected TestableFilesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new TestableFilesystem(Hyde::getInstance());
        $this->filesystem->setTestMediaFiles([]);

        $mock = Mockery::mock(BaseFilesystem::class)->makePartial();
        $mock->shouldReceive('missing')->andReturn(false)->byDefault();
        $mock->shouldReceive('size')->andReturn(100)->byDefault();
        $mock->shouldReceive('hash')->andReturn('hash')->byDefault();

        app()->instance(BaseFilesystem::class, $mock);
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();

        app()->forgetInstance(BaseFilesystem::class);
        app()->forgetInstance(FileFinder::class);

        Hyde::setMediaDirectory('_media');
    }

    public function testAssetsMethodReturnsSameInstanceOnSubsequentCalls(): void
    {
        $firstCall = $this->filesystem->assets();
        $secondCall = $this->filesystem->assets();

        $this->assertSame($firstCall, $secondCall);
    }

    public function testAssetsMethodReturnsEmptyCollectionWhenNoMediaFiles(): void
    {
        $assets = $this->filesystem->assets();

        $this->assertInstanceOf(Collection::class, $assets);
        $this->assertTrue($assets->isEmpty());
    }

    public function testAssetsMethodDiscoversAllMediaFilesRegardlessOfExtension(): void
    {
        $this->filesystem->setTestMediaFiles([
            Hyde::path('_media/image.jpg'),
            Hyde::path('_media/document.pdf'),
            Hyde::path('_media/font.woff2'),
            Hyde::path('_media/data.json'),
            Hyde::path('_media/archive.zip'),
            Hyde::path('_media/file-without-extension'),
        ]);

        $assets = $this->filesystem->assets();

        $this->assertCount(6, $assets);
        $this->assertInstanceOf(MediaFile::class, $assets->get('image.jpg'));
        $this->assertInstanceOf(MediaFile::class, $assets->get('document.pdf'));
        $this->assertInstanceOf(MediaFile::class, $assets->get('font.woff2'));
        $this->assertInstanceOf(MediaFile::class, $assets->get('data.json'));
        $this->assertInstanceOf(MediaFile::class, $assets->get('archive.zip'));
        $this->assertInstanceOf(MediaFile::class, $assets->get('file-without-extension'));
    }

    public function testAssetsMethodPreservesNestedIdentifiers(): void
    {
        $this->filesystem->setTestMediaFiles([
            Hyde::path('_media/images/photo.jpg'),
            Hyde::path('_media/documents/report.pdf'),
            Hyde::path('_media/fonts/vendor/app.woff2'),
        ]);

        $assets = $this->filesystem->assets();

        $this->assertSame([
            'images/photo.jpg',
            'documents/report.pdf',
            'fonts/vendor/app.woff2',
        ], $assets->keys()->all());
    }

    public function testMediaDiscoveryIsRecursiveAndDoesNotFilterByExtension(): void
    {
        $mock = $this->mockFileFinder();

        (new Filesystem(Hyde::getInstance()))->assets();

        $mock->shouldHaveReceived('handle')
            ->once()
            ->with('_media', false, true);
    }

    public function testMediaDiscoveryUsesCustomMediaDirectory(): void
    {
        Hyde::setMediaDirectory('assets');

        $mock = $this->mockFileFinder();

        (new Filesystem(Hyde::getInstance()))->assets();

        $mock->shouldHaveReceived('handle')
            ->once()
            ->with('assets', false, true);
    }

    protected function mockFileFinder(): MockInterface
    {
        $mock = Mockery::mock(FileFinder::class);
        $mock->shouldReceive('handle')->andReturn(collect());

        app()->instance(FileFinder::class, $mock);

        return $mock;
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
}
