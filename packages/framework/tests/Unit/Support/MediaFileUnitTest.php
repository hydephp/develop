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

    protected static string $originalBasePath;

    protected $mockFilesystem;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$originalBasePath = Hyde::getBasePath();

        Hyde::setBasePath('/base/path');
    }

    public static function tearDownAfterClass(): void
    {
        Hyde::setBasePath(static::$originalBasePath);

        parent::tearDownAfterClass();
    }

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

    public function testConstructorWithVariousInputFormats()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make('foo.txt')->path);
        $this->assertSame('_media/foo.txt', MediaFile::make('_media/foo.txt')->path);
        $this->assertSame('_media/foo.txt', MediaFile::make(Hyde::path('_media/foo.txt'))->path);
        $this->assertSame('_media/foo.txt', MediaFile::make('media/foo.txt')->path);
    }

    public function testConstructorWithValidationDisabled()
    {
        MediaFile::$validateExistence = false;
        $this->mockFilesystem->shouldReceive('missing')->never();

        $file = new MediaFile('non_existent_file.txt');
        $this->assertInstanceOf(MediaFile::class, $file);
    }

    public function testConstructorSetsProperties()
    {
        $file = new MediaFile('foo.txt');
        $this->assertNotNull($file->length);
        $this->assertNotNull($file->mimeType);
        $this->assertNotNull($file->hash);
    }

    public function testNormalizePathWithAbsolutePath()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make(Hyde::path('_media/foo.txt'))->path);
    }

    public function testNormalizePathWithRelativePath()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make('foo.txt')->path);
    }

    public function testNormalizePathWithOutputDirectoryPath()
    {
        Hyde::setMediaDirectory('_custom_media');
        $this->assertSame('_custom_media/foo.txt', MediaFile::make('custom_media/foo.txt')->path);
        Hyde::setMediaDirectory('_media'); // Reset to default
    }

    public function testNormalizePathWithAlreadyCorrectFormat()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make('_media/foo.txt')->path);
    }

    public function testNormalizePathWithParentDirectoryReferences()
    {
        $this->assertSame('_media/foo.txt', MediaFile::make('../_media/foo.txt')->path);
        $this->assertSame('_media/baz/../bar/foo.txt', MediaFile::make('_media/baz/../bar/foo.txt')->path); // We don't do anything about this
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

    public function testGetIdentifierWithSubdirectory()
    {
        $this->assertSame('foo/bar', MediaFile::make('foo/bar')->getIdentifier());
    }

    public function testGetIdentifierReturnsIdentifierWithFileExtension()
    {
        $this->assertSame('foo.png', MediaFile::make('foo.png')->getIdentifier());
    }

    public function testGetIdentifierWithSubdirectoryWithFileExtension()
    {
        $this->assertSame('foo/bar.png', MediaFile::make('foo/bar.png')->getIdentifier());
    }

    public function testHelperForMediaPath()
    {
        $this->assertSame('/base/path/_media', MediaFile::sourcePath());
    }

    public function testHelperForMediaPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame('/base/path/_media/foo.css', MediaFile::sourcePath('foo.css'));
    }

    public function testGetMediaPathReturnsAbsolutePath()
    {
        $this->assertSame('/base/path/_media', MediaFile::sourcePath());
    }

    public function testHelperForMediaOutputPath()
    {
        $this->assertSame('/base/path/_site/media', MediaFile::outputPath());
    }

    public function testHelperForMediaOutputPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame('/base/path/_site/media/foo.css', MediaFile::outputPath('foo.css'));
    }

    public function testGetMediaOutputPathReturnsAbsolutePath()
    {
        $this->assertSame('/base/path/_site/media', MediaFile::outputPath());
    }

    public function testCanGetSiteMediaOutputDirectory()
    {
        $this->assertSame('/base/path/_site/media', MediaFile::outputPath());
    }

    public function testGetSiteMediaOutputDirectoryUsesTrimmedVersionOfMediaSourceDirectory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame('/base/path/_site/foo', MediaFile::outputPath());
        Hyde::setMediaDirectory('_media'); // Reset to default
    }

    public function testGetSiteMediaOutputDirectoryUsesConfiguredSiteOutputDirectory()
    {
        Hyde::setOutputDirectory('/base/path/foo');
        Hyde::setMediaDirectory('bar');

        $this->assertSame('/base/path/foo/bar', MediaFile::outputPath());

        Hyde::setOutputDirectory('/base/path/_site'); // Reset to default
        Hyde::setMediaDirectory('_media'); // Reset to default
    }

    public function testSourcePathWithEmptyString()
    {
        $this->assertSame(Hyde::path('_media'), MediaFile::sourcePath(''));
    }

    public function testSourcePathWithSubdirectories()
    {
        $this->assertSame(Hyde::path('_media/foo/bar'), MediaFile::sourcePath('foo/bar'));
    }

    public function testSourcePathWithLeadingSlash()
    {
        $this->assertSame(Hyde::path('_media/foo'), MediaFile::sourcePath('/foo'));
    }

    public function testOutputPathWithEmptyString()
    {
        $this->assertSame(Hyde::sitePath('media'), MediaFile::outputPath(''));
    }

    public function testOutputPathWithSubdirectories()
    {
        $this->assertSame(Hyde::sitePath('media/foo/bar'), MediaFile::outputPath('foo/bar'));
    }

    public function testOutputPathWithLeadingSlash()
    {
        $this->assertSame(Hyde::sitePath('media/foo'), MediaFile::outputPath('/foo'));
    }
}
