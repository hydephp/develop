<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Support;

use Mockery;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem as BaseFilesystem;

/**
 * @covers \Hyde\Support\Filesystem\MediaFile
 *
 * @see \Hyde\Framework\Testing\Feature\Support\MediaFileTest
 */
class MediaFileUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected $mockFilesystem;

    protected function setUp(): void
    {
        parent::setUp();

        MediaFile::$validateExistence = false;

        $this->mockFilesystem = Mockery::mock(BaseFilesystem::class);
        app()->instance(BaseFilesystem::class, $this->mockFilesystem);

        // Set up default expectations for commonly called methods
        $this->mockFilesystem->shouldReceive('isFile')->andReturn(true)->byDefault();
        $this->mockFilesystem->shouldReceive('missing')->andReturn(false)->byDefault();
        $this->mockFilesystem->shouldReceive('extension')->andReturn('txt')->byDefault();
        $this->mockFilesystem->shouldReceive('size')->andReturn(12)->byDefault();
        $this->mockFilesystem->shouldReceive('mimeType')->andReturn('text/plain')->byDefault();
        $this->mockFilesystem->shouldReceive('hash')->andReturn(hash('crc32', 'Hello World!'))->byDefault();
        $this->mockFilesystem->shouldReceive('get')->andReturn('Hello World!')->byDefault();

        // Mock Hyde facade
        $hyde = Mockery::mock(Hyde::kernel())->makePartial();
        $hyde->shouldReceive('assets')->andReturn(collect(['app.css' => new MediaFile('_media/app.css')]))->byDefault();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        MediaFile::$validateExistence = true;
        Mockery::close();
    }

    public function testCanConstruct()
    {
        $file = new MediaFile('foo');

        $this->assertInstanceOf(MediaFile::class, $file);
        $this->assertSame('_media/foo', $file->path);
    }

    public function testCanMake()
    {
        $this->assertEquals(new MediaFile('foo'), MediaFile::make('foo'));
    }

    public function testCanConstructWithNestedPaths()
    {
        $this->assertSame('_media/path/to/file.txt', MediaFile::make('path/to/file.txt')->path);
    }

    public function testPathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make('foo')->path);
    }

    public function testAbsolutePathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make(Hyde::path('foo'))->path);
    }

    public function testMediaPathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make('_media/foo')->path);
    }

    public function testAbsoluteMediaPathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make(Hyde::path('_media/foo'))->path);
    }

    public function testOutputMediaPathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make('media/foo')->path);
    }

    public function testAbsoluteOutputMediaPathIsNormalizedToRelativeMediaPath()
    {
        $this->assertSame('_media/foo', MediaFile::make(Hyde::path('media/foo'))->path);
    }

    public function testCustomMediaPathsAreNormalizedToRelativeCustomizedMediaPath()
    {
        Hyde::setMediaDirectory('bar');

        $this->assertSame('bar/foo', MediaFile::make('foo')->path);
        $this->assertSame('bar/foo', MediaFile::make('bar/foo')->path);
        $this->assertSame('bar/foo', MediaFile::make(Hyde::path('foo'))->path);

        Hyde::setMediaDirectory('_bar');

        $this->assertSame('_bar/foo', MediaFile::make('foo')->path);
        $this->assertSame('_bar/foo', MediaFile::make('_bar/foo')->path);
        $this->assertSame('_bar/foo', MediaFile::make(Hyde::path('_bar/foo'))->path);
        $this->assertSame('_bar/foo', MediaFile::make('bar/foo')->path);
        $this->assertSame('_bar/foo', MediaFile::make(Hyde::path('foo'))->path);

        Hyde::setMediaDirectory('_media');
    }

    public function testGetNameReturnsNameOfFile()
    {
        $this->assertSame('foo.txt', MediaFile::make('foo.txt')->getName());
        $this->assertSame('bar.txt', MediaFile::make('foo/bar.txt')->getName());
    }

    public function testGetPathReturnsPathOfFile()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make('foo.txt')->getPath());
        $this->assertSame('_media/foo/bar.txt', MediaFile::make('foo/bar.txt')->getPath());
    }

    public function testGetAbsolutePathReturnsAbsolutePathOfFile()
    {
        $this->assertSame(Hyde::path('_media/foo.txt'), MediaFile::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('_media/foo/bar.txt'), MediaFile::make('foo/bar.txt')->getAbsolutePath());
    }

    public function testGetContentsReturnsContentsOfFile()
    {
        $this->mockFilesystem->shouldReceive('get')
            ->andReturn('foo bar')
            ->once();

        $this->assertSame('foo bar', MediaFile::make('foo.txt')->getContents());
    }

    public function testGetExtensionReturnsExtensionOfFile()
    {
        $this->mockFilesystem->shouldReceive('extension')
            ->with(Hyde::path('_media/foo.txt'))
            ->andReturn('txt');

        $this->mockFilesystem->shouldReceive('extension')
            ->with(Hyde::path('_media/foo.png'))
            ->andReturn('png');

        $this->assertSame('txt', MediaFile::make('foo.txt')->getExtension());
        $this->assertSame('png', MediaFile::make('foo.png')->getExtension());
    }

    public function testToArrayReturnsArrayOfFileProperties()
    {
        $this->mockFilesystem->shouldReceive('size')
            ->with(Hyde::path('_media/foo.txt'))
            ->andReturn(7);

        $this->mockFilesystem->shouldReceive('mimeType')
            ->with(Hyde::path('_media/foo.txt'))
            ->andReturn('text/plain');

        $this->mockFilesystem->shouldReceive('hash')
            ->with(Hyde::path('_media/foo.txt'), 'crc32')
            ->andReturn(hash('crc32', 'foo bar'));

        $this->assertSame([
            'name' => 'foo.txt',
            'path' => '_media/foo.txt',
            'length' => 7,
            'mimeType' => 'text/plain',
            'hash' => hash('crc32', 'foo bar'),
        ], MediaFile::make('foo.txt')->toArray());
    }

    public function testGetContentLength()
    {
        $this->mockFilesystem->shouldReceive('size')
            ->with(Hyde::path('_media/foo'))
            ->andReturn(12);

        $this->assertSame(12, MediaFile::make('foo')->getContentLength());
    }

    public function testGetMimeType()
    {
        $this->mockFilesystem->shouldReceive('mimeType')
            ->with(Hyde::path('_media/foo.txt'))
            ->andReturn('text/plain');

        $this->assertSame('text/plain', MediaFile::make('foo.txt')->getMimeType());
    }

    public function testAllHelperReturnsAllMediaFiles()
    {
        $this->assertEquals([
            'app.css' => new MediaFile('_media/app.css'),
        ], MediaFile::all()->all());
    }

    public function testFilesHelperReturnsAllMediaFiles()
    {
        $this->assertSame(['app.css'], MediaFile::files());
    }

    public function testGetHashReturnsHash()
    {
        $this->mockFilesystem->shouldReceive('hash')
            ->with(Hyde::path('_media/foo.txt'), 'crc32')
            ->andReturn(hash('crc32', 'Hello World!'));

        $this->assertSame(hash('crc32', 'Hello World!'), MediaFile::make('foo.txt')->getHash());
    }

    public function testExceptionIsThrownWhenConstructingFileThatDoesNotExist()
    {
        MediaFile::$validateExistence = true;

        $this->mockFilesystem->shouldReceive('missing')
            ->with(Hyde::path('_media/foo'))
            ->andReturn(true);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_media/foo] not found.');

        MediaFile::make('foo');
    }

    public function testExceptionIsNotThrownWhenConstructingFileThatDoesExist()
    {
        MediaFile::$validateExistence = true;

        $this->mockFilesystem->shouldReceive('missing')
            ->with(Hyde::path('_media/foo'))
            ->andReturn(false);

        $this->assertInstanceOf(MediaFile::class, MediaFile::make('foo'));
    }
}
