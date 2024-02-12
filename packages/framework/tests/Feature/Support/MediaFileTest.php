<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\MediaFile
 */
class MediaFileTest extends TestCase
{
    public function testCanConstruct()
    {
        $file = new MediaFile('foo');
        $this->assertInstanceOf(MediaFile::class, $file);
        $this->assertSame('foo', $file->path);
    }

    public function can_make()
    {
        $this->assertEquals(new MediaFile('foo'), MediaFile::make('foo'));
    }

    public function testCanConstructWithNestedPaths()
    {
        $this->assertEquals('path/to/file.txt', MediaFile::make('path/to/file.txt')->path);
    }

    public function testAbsolutePathIsNormalizedToRelative()
    {
        $this->assertEquals('foo', MediaFile::make(Hyde::path('foo'))->path);
    }

    public function testGetNameReturnsNameOfFile()
    {
        $this->assertSame('foo.txt', MediaFile::make('foo.txt')->getName());
        $this->assertSame('bar.txt', MediaFile::make('foo/bar.txt')->getName());
    }

    public function testGetPathReturnsPathOfFile()
    {
        $this->assertSame('foo.txt', MediaFile::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', MediaFile::make('foo/bar.txt')->getPath());
    }

    public function testGetAbsolutePathReturnsAbsolutePathOfFile()
    {
        $this->assertSame(Hyde::path('foo.txt'), MediaFile::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), MediaFile::make('foo/bar.txt')->getAbsolutePath());
    }

    public function testGetContentsReturnsContentsOfFile()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', MediaFile::make('foo.txt')->getContents());
    }

    public function testGetExtensionReturnsExtensionOfFile()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', MediaFile::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', MediaFile::make('foo.png')->getExtension());
    }

    public function testToArrayReturnsArrayOfFileProperties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name' => 'foo.txt',
            'path' => 'foo.txt',
            'length' => 7,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo.txt')->toArray());
    }

    public function testToArrayWithEmptyFileWithNoExtension()
    {
        $this->file('foo', 'foo bar');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
            'length' => 7,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo')->toArray());
    }

    public function testToArrayWithFileInSubdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
            'length' => 0,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo/bar.txt')->toArray());
        Filesystem::unlink('foo/bar.txt');
        rmdir(Hyde::path('foo'));
    }

    public function testGetContentLength()
    {
        $this->file('foo', 'Hello World!');
        $this->assertSame(12, MediaFile::make('foo')->getContentLength());
    }

    public function testGetContentLengthWithEmptyFile()
    {
        $this->file('foo', '');
        $this->assertSame(0, MediaFile::make('foo')->getContentLength());
    }

    public function testGetContentLengthWithDirectory()
    {
        $this->directory('foo');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [foo] not found.');
        MediaFile::make('foo')->getContentLength();
    }

    public function testGetContentLengthWithNonExistentFile()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [foo] not found.');
        MediaFile::make('foo')->getContentLength();
    }

    public function testGetMimeType()
    {
        $this->file('foo.txt', 'Hello World!');
        $this->assertSame('text/plain', MediaFile::make('foo.txt')->getMimeType());
    }

    public function testGetMimeTypeWithoutExtension()
    {
        $this->file('foo', 'Hello World!');
        $this->assertSame('text/plain', MediaFile::make('foo')->getMimeType());
    }

    public function testGetMimeTypeWithEmptyFile()
    {
        $this->file('foo', '');
        $this->assertSame('application/x-empty', MediaFile::make('foo')->getMimeType());
    }

    public function testGetMimeTypeWithDirectory()
    {
        $this->directory('foo');
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
        ], MediaFile::all());
    }

    public function testAllHelperDoesNotIncludeNonMediaFiles()
    {
        $this->file('_media/foo.blade.php');
        $this->assertEquals([
            'app.css' => new MediaFile('_media/app.css'),
        ], MediaFile::all());
    }

    public function testFilesHelper()
    {
        $this->assertSame(['app.css'], MediaFile::files());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', MediaFile::make('foo')->getIdentifier());
    }

    public function testGetIdentifierWithSubdirectory()
    {
        $this->assertSame('foo/bar', MediaFile::make('foo/bar')->getIdentifier());
    }
}
