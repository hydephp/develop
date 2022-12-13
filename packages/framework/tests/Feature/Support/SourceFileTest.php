<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\SourceFile
 */
class SourceFileTest extends TestCase
{
    public function test_make_method_creates_new_file_object_with_path()
    {
        $file = SourceFile::make('path/to/file.txt');
        $this->assertInstanceOf(SourceFile::class, $file);
        $this->assertEquals('path/to/file.txt', $file->path);
    }

    public function test_make_method_gives_same_result_as_constructor()
    {
        $this->assertEquals(SourceFile::make('foo'), new SourceFile('foo'));
    }

    public function test_absolute_path_is_normalized_to_relative()
    {
        $this->assertEquals('foo', SourceFile::make(Hyde::path('foo'))->path);
    }

    public function test_to_string_returns_path()
    {
        $this->assertSame('foo', (string) SourceFile::make('foo'));
    }

    public function test_get_name_returns_name_of_file()
    {
        $this->assertSame('foo.txt', SourceFile::make('foo.txt')->getName());
        $this->assertSame('bar.txt', SourceFile::make('foo/bar.txt')->getName());
    }

    public function test_get_path_returns_path_of_file()
    {
        $this->assertSame('foo.txt', SourceFile::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', SourceFile::make('foo/bar.txt')->getPath());
    }

    public function test_get_absolute_path_returns_absolute_path_of_file()
    {
        $this->assertSame(Hyde::path('foo.txt'), SourceFile::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), SourceFile::make('foo/bar.txt')->getAbsolutePath());
    }

    public function test_get_contents_returns_contents_of_file()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', SourceFile::make('foo.txt')->getContents());
    }

    public function test_get_extension_returns_extension_of_file()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', SourceFile::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', SourceFile::make('foo.png')->getExtension());
    }

    public function test_to_array_returns_array_of_file_properties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name'     => 'foo.txt',
            'path'     => 'foo.txt',
            'model' => 'Hyde\Pages\Concerns\HydePage',
        ], SourceFile::make('foo.txt')->toArray());
    }

    public function test_to_array_with_empty_file_with_no_extension()
    {
        $this->file('foo');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
            'model' => 'Hyde\Pages\Concerns\HydePage',
        ], SourceFile::make('foo')->toArray());
    }

    public function test_to_array_with_file_in_subdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
            'model' => 'Hyde\Pages\Concerns\HydePage',
        ], SourceFile::make('foo/bar.txt')->toArray());
        unlink(Hyde::path('foo/bar.txt'));
        rmdir(Hyde::path('foo'));
    }

    public function test_without_directory_prefix_retains_subdirectories()
    {
        $this->assertSame('foo/bar/baz.txt',
            SourceFile::make('foo/bar/baz.txt', MarkdownPage::class)->withoutDirectoryPrefix()
        );

        $this->assertSame('foo/bar.txt',
            SourceFile::make('_pages/foo/bar.txt', MarkdownPage::class)->withoutDirectoryPrefix()
        );
    }
}
