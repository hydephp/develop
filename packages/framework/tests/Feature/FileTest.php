<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\File;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\File
 */
class FileTest extends TestCase
{
    // make
    public function test_make_method_creates_new_file_object_with_path()
    {
        $file = File::make('path/to/file.txt');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('path/to/file.txt', $file->path);
    }

    // make alias constructor
    public function test_make_method_gives_same_result_as_constructor()
    {
        $this->assertEquals(File::make('foo'), new File('foo'));
    }

    public function test_absolute_path_is_normalized_to_relative()
    {
        $this->assertEquals('foo', File::make(Hyde::path('foo'))->path);
    }

    public function test_to_string_returns_path()
    {
        $this->assertSame('foo', (string) File::make('foo'));
    }

    public function test_belongs_to_returns_null_when_no_relation_or_parameter_is_set()
    {
        $this->assertNull(File::make('foo')->belongsTo());
    }

    public function test_belongs_to_returns_class_name_when_relation_is_set()
    {
        $this->assertSame('bar', File::make('foo', 'bar')->belongsTo());
    }

    public function test_belongs_to_returns_self_when_parameter_is_set()
    {
        $this->assertInstanceOf(File::class, File::make('foo')->belongsTo('bar'));
        $this->assertEquals(File::make('foo', 'bar'), File::make('foo')->belongsTo('bar'));
    }

    public function test_class_name_can_be_set_using_belongs_to_method()
    {
        $this->assertSame('baz', File::make('foo', 'bar')->belongsTo('baz')->belongsTo());
    }

    public function test_get_name_returns_name_of_file()
    {
        $this->assertSame('foo.txt', File::make('foo.txt')->getName());
        $this->assertSame('bar.txt', File::make('foo/bar.txt')->getName());
    }

    public function test_get_path_returns_path_of_file()
    {
        $this->assertSame('foo.txt', File::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', File::make('foo/bar.txt')->getPath());
    }

    public function test_get_absolute_path_returns_absolute_path_of_file()
    {
        $this->assertSame(Hyde::path('foo.txt'), File::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), File::make('foo/bar.txt')->getAbsolutePath());
    }

    public function test_get_contents_returns_contents_of_file()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', File::make('foo.txt')->getContents());
    }

    public function test_get_content_length_returns_length_of_file()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame(7, File::make('foo.txt')->getContentLength());
    }

    public function test_get_extension_returns_extension_of_file()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', File::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', File::make('foo.png')->getExtension());
    }

    public function test_get_mime_type_returns_mime_type_of_file_using_lookup_table()
    {
        $lookup = [
            'txt'  => 'text/plain',
            'md'   => 'text/markdown',
            'html' => 'text/html',
            'css'  => 'text/css',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'json' => 'application/json',
            'js'   => 'application/javascript',
        ];

        foreach ($lookup as $extension => $mimeType) {
            $this->assertSame($mimeType, File::make('foo.'.$extension)->getMimeType());
        }
    }

    public function test_get_mime_type_returns_filetype_if_file_exists()
    {
        $this->file('foo.bar', 'foo');
        $this->assertSame('text/plain', File::make('foo.bar')->getMimeType());

        $this->file('foo');
        $this->assertSame('application/x-empty', File::make('foo')->getMimeType());
    }

    public function test_get_mime_type_returns_text_plain_if_file_does_not_exist_and_is_not_in_lookup_table()
    {
        $this->assertSame('text/plain', File::make('foo')->getMimeType());
        $this->assertSame('text/plain', File::make('foo.bar')->getMimeType());
    }

    public function test_to_array_returns_array_of_file_properties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
         'name'     => 'foo.txt',
         'path'     => 'foo.txt',
         'contents' => 'foo bar',
         'length'   => 7,
         'mimeType' => 'text/plain',
         'model'    => null,
        ], File::make('foo.txt')->toArray());

        $this->assertSame([
            'path' => 'foo',
            'model'    => null,
        ], File::make('foo')->toArray());

        $this->assertSame([
            'path' => 'foo/bar.txt',
            'model'    => 'baz',
        ], File::make('foo/bar.txt', 'baz')->toArray());
    }

    public function test_without_directory_prefix_returns_file_without_directory_prefix()
    {
        $this->assertSame('baz.txt', File::make('foo/bar/baz.txt')->withoutDirectoryPrefix());
    }

    public function test_without_directory_prefix_retains_subdirectories_when_a_page_model_class_is_set()
    {
        $this->assertSame('foo/bar.txt',
            File::make('_pages/foo/bar.txt', MarkdownPage::class)->withoutDirectoryPrefix()
        );
    }
}
