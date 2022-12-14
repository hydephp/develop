<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\SourceFile
 */
class SourceFileTest extends TestCase
{
    public function test_can_construct()
    {
        $file = new SourceFile('foo');
        $this->assertInstanceOf(SourceFile::class, $file);

        $this->assertSame('foo', $file->path);
        $this->assertSame(HydePage::class, $file->model);
    }

    public function test_can_construct_with_model_class()
    {
        $file = new SourceFile('foo', MarkdownPage::class);
        $this->assertInstanceOf(SourceFile::class, $file);

        $this->assertSame('foo', $file->path);
        $this->assertSame(MarkdownPage::class, $file->model);
    }

    public function can_make()
    {
        $this->assertEquals(new SourceFile('foo'), SourceFile::make('foo'));
    }

    public function test_can_make_with_model_class()
    {
        $this->assertEquals(new SourceFile('foo', MarkdownPage::class),
            SourceFile::make('foo', MarkdownPage::class));
    }

    public function test_can_construct_with_nested_paths()
    {
        $this->assertEquals('path/to/file.txt', SourceFile::make('path/to/file.txt')->path);
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
            'model' => HydePage::class,
        ], SourceFile::make('foo.txt')->toArray());
    }

    public function test_to_array_with_empty_file_with_no_extension()
    {
        $this->file('foo');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
            'model' => HydePage::class,
        ], SourceFile::make('foo')->toArray());
    }

    public function test_to_array_with_file_in_subdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
            'model' => HydePage::class,
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
