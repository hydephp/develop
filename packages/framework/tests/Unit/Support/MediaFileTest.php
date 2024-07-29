<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Support;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\CreatesTemporaryFiles;

/**
 * @covers \Hyde\Support\Filesystem\MediaFile
 */
class MediaFileTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        MediaFile::$validateExistence = false;
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();

        MediaFile::$validateExistence = true;
    }

    /** @deprecated */
    public function testDiscoveryBenchmark()
    {
        $this->markTestSkipped('Uncomment this line to run the benchmark.');

        $urls = [
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/austria.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/boat.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/croatia.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/fireworks.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/hallstatt.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/lemonade.jpg',
            'https://raw.githubusercontent.com/caendesilva/RandomDatasets/master/Media/photographer.jpg',
        ];

        foreach ($urls as $url) {
            $this->file('_media/'.basename($url), file_get_contents($url));
        }

        $this->file('_media/foo.css', 'body { color: red; }');
        $this->file('_media/foo.js', 'console.log("Hello, World!");');
        $this->file('_media/nested/foo.css', 'foo');
        $this->file('_media/empty.css', '');
        $this->file('_media/ignored', 'ignored');
        $this->directory('_media/empty');
        $this->file('_media/large.css', str_repeat('a', 1024 * 1024 * 10)); // 10 MB

        // Warm up the classloader / cache
        echo 'Warmup: '.\Illuminate\Support\Benchmark::measure(function () {
            MediaFile::all();
        })."ms\n";

        echo 'Benchmark: '.\Illuminate\Support\Benchmark::measure(function () {
            MediaFile::all();
        }, 1000)."ms avg/1000/its\n";
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

    public function testCustomMediaPathsAreNormalizedToRelativeCustomizedMediaPath()
    {
        Hyde::setMediaDirectory('bar');

        $this->assertSame('bar/foo', MediaFile::make('foo')->path);
        $this->assertSame('bar/foo', MediaFile::make('bar/foo')->path);
        $this->assertSame('bar/foo', MediaFile::make(Hyde::path('foo'))->path);

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
        $this->file('_media/foo.txt', 'foo bar');
        $this->assertSame('foo bar', MediaFile::make('foo.txt')->getContents());
    }

    public function testGetExtensionReturnsExtensionOfFile()
    {
        $this->file('_media/foo.txt', 'foo');
        $this->assertSame('txt', MediaFile::make('foo.txt')->getExtension());

        $this->file('_media/foo.png', 'foo');
        $this->assertSame('png', MediaFile::make('foo.png')->getExtension());
    }

    public function testToArrayReturnsArrayOfFileProperties()
    {
        $this->file('_media/foo.txt', 'foo bar');

        $this->assertSame([
            'name' => 'foo.txt',
            'path' => '_media/foo.txt',
            'length' => 7,
            'mimeType' => 'text/plain',
            'hash' => hash('crc32', 'foo bar'),
        ], MediaFile::make('foo.txt')->toArray());
    }

    public function testToArrayWithEmptyFileWithNoExtension()
    {
        $this->file('_media/foo', 'foo bar');

        $this->assertSame([
            'name' => 'foo',
            'path' => '_media/foo',
            'length' => 7,
            'mimeType' => 'text/plain',
            'hash' => hash('crc32', 'foo bar'),
        ], MediaFile::make('foo')->toArray());
    }

    public function testToArrayWithFileInSubdirectory()
    {
        mkdir(Hyde::path('_media/foo'));
        touch(Hyde::path('_media/foo/bar.txt'));

        $this->assertSame([
            'name' => 'bar.txt',
            'path' => '_media/foo/bar.txt',
            'length' => 0,
            'mimeType' => 'text/plain',
            'hash' => hash('crc32', ''),
        ], MediaFile::make('foo/bar.txt')->toArray());

        Filesystem::unlink('_media/foo/bar.txt');
        rmdir(Hyde::path('_media/foo'));
    }

    public function testGetContentLength()
    {
        $this->file('_media/foo', 'Hello World!');
        $this->assertSame(12, MediaFile::make('foo')->getContentLength());
    }

    public function testGetContentLengthWithEmptyFile()
    {
        $this->file('_media/foo', '');
        $this->assertSame(0, MediaFile::make('foo')->getContentLength());
    }

    public function testGetContentLengthWithDirectory()
    {
        $this->directory('_media/foo');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_media/foo] not found.');

        MediaFile::make('foo')->getContentLength();
    }

    public function testGetContentLengthWithNonExistentFile()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_media/foo] not found.');

        MediaFile::make('foo')->getContentLength();
    }

    public function testGetMimeType()
    {
        $this->file('_media/foo.txt', 'Hello World!');
        $this->assertSame('text/plain', MediaFile::make('foo.txt')->getMimeType());
    }

    public function testGetMimeTypeWithoutExtension()
    {
        $this->file('_media/foo', 'Hello World!');
        $this->assertSame('text/plain', MediaFile::make('foo')->getMimeType());
    }

    public function testGetMimeTypeWithEmptyFile()
    {
        $this->file('_media/foo', '');
        $this->assertSame('application/x-empty', MediaFile::make('foo')->getMimeType());
    }

    public function testGetMimeTypeWithDirectory()
    {
        $this->directory('_media/foo');
        $this->assertSame('directory', MediaFile::make('foo')->getMimeType());
    }

    public function testGetMimeTypeWithNonExistentFile()
    {
        $this->assertSame('text/plain', MediaFile::make('foo')->getMimeType());
    }

    public function testAllHelperReturnsAllMediaFiles()
    {
        $this->assertEquals([
            'app.css' => new MediaFile('_media/app.css'),
        ], MediaFile::all()->all());
    }

    public function testAllHelperDoesNotIncludeNonMediaFiles()
    {
        $this->file('_media/foo.blade.php');

        $this->assertEquals([
            'app.css' => new MediaFile('_media/app.css'),
        ], MediaFile::all()->all());
    }

    public function testFilesHelperReturnsAllMediaFiles()
    {
        $this->assertSame(['app.css'], MediaFile::files());
    }

    public function testGetIdentifierReturnsIdentifier()
    {
        $this->assertSame('foo', MediaFile::make('foo')->getIdentifier());
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
        $this->assertSame(Hyde::path('_media'), MediaFile::sourcePath());
    }

    public function testHelperForMediaPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame(Hyde::path('_media/foo.css'), MediaFile::sourcePath('foo.css'));
    }

    public function testGetMediaPathReturnsAbsolutePath()
    {
        $this->assertSame(Hyde::path('_media'), MediaFile::sourcePath());
    }

    public function testHelperForMediaOutputPath()
    {
        $this->assertSame(Hyde::path('_site/media'), MediaFile::outputPath());
    }

    public function testHelperForMediaOutputPathReturnsPathToFileWithinTheDirectory()
    {
        $this->assertSame(Hyde::path('_site/media/foo.css'), MediaFile::outputPath('foo.css'));
    }

    public function testGetMediaOutputPathReturnsAbsolutePath()
    {
        $this->assertSame(Hyde::path('_site/media'), MediaFile::outputPath());
    }

    public function testCanGetSiteMediaOutputDirectory()
    {
        $this->assertSame(Hyde::path('_site/media'), MediaFile::outputPath());
    }

    public function testGetSiteMediaOutputDirectoryUsesTrimmedVersionOfMediaSourceDirectory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame(Hyde::path('_site/foo'), MediaFile::outputPath());
    }

    public function testGetSiteMediaOutputDirectoryUsesConfiguredSiteOutputDirectory()
    {
        Hyde::setOutputDirectory(Hyde::path('foo'));
        Hyde::setMediaDirectory('bar');

        $this->assertSame(Hyde::path('foo/bar'), MediaFile::outputPath());

        Hyde::setOutputDirectory(Hyde::path('_site'));
        Hyde::setMediaDirectory('_media');
    }

    public function testGetHash()
    {
        $this->file('_media/foo.txt', 'Hello World!');

        $this->assertSame(hash('crc32', 'Hello World!'), MediaFile::make('foo.txt')->getHash());
    }
}
