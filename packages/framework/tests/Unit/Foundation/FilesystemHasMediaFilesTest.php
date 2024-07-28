<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;
use Mockery;

/**
 * @covers \Hyde\Foundation\Kernel\Filesystem
 * @covers \Hyde\Foundation\Concerns\HasMediaFiles
 */
class FilesystemHasMediaFilesTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem(Hyde::getInstance());
    }

    public function testAssetsMethodReturnsSameInstanceOnSubsequentCalls()
    {
        $firstCall = $this->filesystem->assets();
        $secondCall = $this->filesystem->assets();

        $this->assertSame($firstCall, $secondCall);
    }

    public function testAssetsMethodReturnsEmptyCollectionWhenNoMediaFiles()
    {
        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [];
        });

        $assets = $this->filesystem->assets();

        $this->assertInstanceOf(Collection::class, $assets);
        $this->assertTrue($assets->isEmpty());
    }

    public function testAssetsMethodWithCustomMediaExtensions()
    {
        $this->app['config']->set('hyde.media_extensions', ['jpg', 'png']);

        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [
                Hyde::path('_media/image1.jpg'),
                Hyde::path('_media/image2.png'),
                Hyde::path('_media/document.pdf'), // This should be excluded
            ];
        });

        $assets = $this->filesystem->assets();

        $this->assertCount(2, $assets);
        $this->assertTrue($assets->has('image1.jpg'));
        $this->assertTrue($assets->has('image2.png'));
        $this->assertFalse($assets->has('document.pdf'));
    }

    public function testAssetsMethodWithNestedDirectories()
    {
        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [
                Hyde::path('_media/images/photo.jpg'),
                Hyde::path('_media/documents/report.pdf'),
            ];
        });

        $assets = $this->filesystem->assets();

        $this->assertCount(2, $assets);
        $this->assertTrue($assets->has('images/photo.jpg'));
        $this->assertTrue($assets->has('documents/report.pdf'));
    }

    public function testAssetsMethodWithInvalidMediaFile()
    {
        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [
                Hyde::path('_media/valid.jpg'),
                Hyde::path('_media/invalid'), // File without extension
            ];
        });

        $assets = $this->filesystem->assets();

        $this->assertCount(1, $assets);
        $this->assertTrue($assets->has('valid.jpg'));
        $this->assertFalse($assets->has('invalid'));
    }

    public function testGetMediaGlobPatternWithCustomMediaDirectory()
    {
        $this->app['config']->set('hyde.media_directory', 'custom_media');

        $pattern = $this->invokeProtectedMethod(Filesystem::class, 'getMediaGlobPattern');

        $this->assertStringContainsString('custom_media/', $pattern);
    }

    public function testGetMediaGlobPatternWithCustomExtensions()
    {
        $this->app['config']->set('hyde.media_extensions', ['gif', 'svg']);

        $pattern = $this->invokeProtectedMethod(Filesystem::class, 'getMediaGlobPattern');

        $this->assertStringContainsString('{gif,svg}', $pattern);
    }

    public function testDiscoverMediaFilesWithEmptyResult()
    {
        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [];
        });

        $result = $this->invokeProtectedMethod(Filesystem::class, 'discoverMediaFiles');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function testDiscoverMediaFilesWithMultipleFiles()
    {
        $this->mockStaticMethod(Filesystem::class, 'getMediaFiles', function () {
            return [
                Hyde::path('_media/image.jpg'),
                Hyde::path('_media/document.pdf'),
            ];
        });

        $result = $this->invokeProtectedMethod(Filesystem::class, 'discoverMediaFiles');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(MediaFile::class, $result->get('image.jpg'));
        $this->assertInstanceOf(MediaFile::class, $result->get('document.pdf'));
    }

    protected function mockStaticMethod($class, $method, $return)
    {
        $mock = Mockery::mock('alias:' . $class);
        $mock->shouldReceive($method)->andReturn($return);
    }

    protected function invokeProtectedMethod($class, $method, array $args = [])
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }
}
