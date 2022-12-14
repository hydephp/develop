<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Hyde;
use Hyde\Support\Filesystem\ProjectFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\ProjectFile
 */
class ProjectFileTest extends TestCase
{
    public function test_can_construct()
    {
        $file = new ProjectFileTestClass('foo');
        $this->assertInstanceOf(ProjectFileTestClass::class, $file);
        $this->assertSame('foo', $file->path);
    }

    public function can_make()
    {
        $this->assertEquals(new ProjectFileTestClass('foo'), ProjectFileTestClass::make('foo'));
    }

    public function test_can_construct_with_nested_paths()
    {
        $this->assertEquals('path/to/file.txt', ProjectFileTestClass::make('path/to/file.txt')->path);
    }

    public function test_absolute_path_is_normalized_to_relative()
    {
        $this->assertEquals('foo', ProjectFileTestClass::make(Hyde::path('foo'))->path);
    }

    public function test_to_string_returns_path()
    {
        $this->assertSame('foo', (string) ProjectFileTestClass::make('foo'));
    }

    public function test_get_name_returns_name_of_file()
    {
        $this->assertSame('foo.txt', ProjectFileTestClass::make('foo.txt')->getName());
        $this->assertSame('bar.txt', ProjectFileTestClass::make('foo/bar.txt')->getName());
    }

    public function test_get_path_returns_path_of_file()
    {
        $this->assertSame('foo.txt', ProjectFileTestClass::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', ProjectFileTestClass::make('foo/bar.txt')->getPath());
    }

    public function test_get_absolute_path_returns_absolute_path_of_file()
    {
        $this->assertSame(Hyde::path('foo.txt'), ProjectFileTestClass::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), ProjectFileTestClass::make('foo/bar.txt')->getAbsolutePath());
    }

    public function test_get_contents_returns_contents_of_file()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', ProjectFileTestClass::make('foo.txt')->getContents());
    }

    public function test_get_extension_returns_extension_of_file()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', ProjectFileTestClass::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', ProjectFileTestClass::make('foo.png')->getExtension());
    }

    public function test_to_array_returns_array_of_file_properties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name'     => 'foo.txt',
            'path'     => 'foo.txt',
        ], ProjectFileTestClass::make('foo.txt')->toArray());
    }

    public function test_to_array_with_empty_file_with_no_extension()
    {
        $this->file('foo');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
        ], ProjectFileTestClass::make('foo')->toArray());
    }

    public function test_to_array_with_file_in_subdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
        ], ProjectFileTestClass::make('foo/bar.txt')->toArray());
        unlink(Hyde::path('foo/bar.txt'));
        rmdir(Hyde::path('foo'));
    }
}

class ProjectFileTestClass extends ProjectFile
{
    //
}
