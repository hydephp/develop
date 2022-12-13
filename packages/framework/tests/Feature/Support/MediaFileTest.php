<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Hyde;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\TestCase;
use RuntimeException;

/**
 * @covers \Hyde\Support\Filesystem\MediaFile
 */
class MediaFileTest extends TestCase
{
    public function test_can_construct()
    {
        $file = new MediaFile('foo');
        $this->assertInstanceOf(MediaFile::class, $file);
        $this->assertSame('foo', $file->path);
    }

    public function can_make()
    {
        $this->assertEquals(new MediaFile('foo'), MediaFile::make('foo'));
    }

    public function test_can_construct_with_nested_paths()
    {
        $this->assertEquals('path/to/file.txt', MediaFile::make('path/to/file.txt')->path);
    }

    public function test_absolute_path_is_normalized_to_relative()
    {
        $this->assertEquals('foo', MediaFile::make(Hyde::path('foo'))->path);
    }

    public function test_to_string_returns_path()
    {
        $this->assertSame('foo', (string) MediaFile::make('foo'));
    }

    public function test_get_name_returns_name_of_file()
    {
        $this->assertSame('foo.txt', MediaFile::make('foo.txt')->getName());
        $this->assertSame('bar.txt', MediaFile::make('foo/bar.txt')->getName());
    }

    public function test_get_path_returns_path_of_file()
    {
        $this->assertSame('foo.txt', MediaFile::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', MediaFile::make('foo/bar.txt')->getPath());
    }

    public function test_get_absolute_path_returns_absolute_path_of_file()
    {
        $this->assertSame(Hyde::path('foo.txt'), MediaFile::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), MediaFile::make('foo/bar.txt')->getAbsolutePath());
    }

    public function test_get_contents_returns_contents_of_file()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', MediaFile::make('foo.txt')->getContents());
    }

    public function test_get_extension_returns_extension_of_file()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', MediaFile::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', MediaFile::make('foo.png')->getExtension());
    }

    public function test_to_array_returns_array_of_file_properties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name'     => 'foo.txt',
            'path'     => 'foo.txt',
            'length' => 7,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo.txt')->toArray());
    }

    public function test_to_array_with_empty_file_with_no_extension()
    {
        $this->file('foo', 'foo bar');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
            'length' => 7,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo')->toArray());
    }

    public function test_to_array_with_file_in_subdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
            'length' => 0,
            'mimeType' => 'text/plain',
        ], MediaFile::make('foo/bar.txt')->toArray());
        unlink(Hyde::path('foo/bar.txt'));
        rmdir(Hyde::path('foo'));
    }

    public function test_getContentLength()
    {
        $this->file('foo', 'Hello World!');
        $this->assertSame(12, MediaFile::make('foo')->getContentLength());
    }

    public function test_getContentLength_with_empty_file()
    {
        $this->file('foo', '');
        $this->assertSame(0, MediaFile::make('foo')->getContentLength());
    }

    public function test_getContentLength_with_directory()
    {
        $this->directory('foo');
        $this->assertSame(0, MediaFile::make('foo')->getContentLength());
    }

    public function test_getContentLength_with_non_existent_file()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not get the content length of file 'foo', are you sure it exists?");
        MediaFile::make('foo')->getContentLength();
    }
}
