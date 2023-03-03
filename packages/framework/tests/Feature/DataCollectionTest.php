<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Support\DataCollection;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\DataCollection
 *
 * @see \Hyde\Framework\Testing\Unit\DataCollectionUnitTest
 */
class DataCollectionTest extends TestCase
{
    public function test_find_markdown_files_method_returns_empty_array_if_the_specified_directory_does_not_exist()
    {
        $class = new DataCollection();
        $this->assertIsArray(DataCollection::markdown('foo')->keys()->toArray());
        $this->assertEmpty(DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_find_markdown_files_method_returns_empty_array_if_no_files_are_found_in_specified_directory()
    {
        $this->directory('resources/collections/foo');

        $class = new DataCollection();
        $this->assertIsArray(DataCollection::markdown('foo')->keys()->toArray());
        $this->assertEmpty(DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_find_markdown_files_method_returns_an_array_of_markdown_files_in_the_specified_directory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertSame([
            'foo/bar.md',
            'foo/foo.md',
        ], DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_find_markdown_files_method_does_not_include_files_in_subdirectories()
    {
        $this->directory('resources/collections/foo');
        $this->directory('resources/collections/foo/bar');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar/bar.md');

        $this->assertSame([
            'foo/foo.md',
        ], DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_find_markdown_files_method_does_not_include_files_with_extensions_other_than_md()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.txt');

        $this->assertSame([
            'foo/foo.md',
        ], DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_find_markdown_files_method_does_not_remove_files_starting_with_an_underscore()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/_foo.md');

        $this->assertSame([
            'foo/_foo.md',
        ], DataCollection::markdown('foo')->keys()->toArray());
    }

    public function test_static_markdown_helper_discovers_and_parses_markdown_files_in_the_specified_directory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertEquals([
            'foo/foo.md' => new MarkdownDocument([], ''),
            'foo/bar.md' => new MarkdownDocument([], ''),
        ], DataCollection::markdown('foo')->toArray());
    }

    public function test_static_markdown_helper_doest_not_ignore_files_starting_with_an_underscore()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/_bar.md');

        $this->assertCount(2, DataCollection::markdown('foo'));
    }

    public function test_source_directory_can_be_changed()
    {
        DataCollection::$sourceDirectory = 'foo';
        $this->directory('foo/bar');
        $this->file('foo/bar/foo.md');

        $this->assertSame([
            'bar/foo.md',
        ], DataCollection::markdown('bar')->keys()->toArray());

        DataCollection::$sourceDirectory = 'resources/collections';
    }

    public function test_source_directory_is_automatically_added_if_missing()
    {
        rmdir(Hyde::path('resources/collections'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/collections'));

        DataCollection::markdown('foo');

        $this->assertDirectoryExists(Hyde::path('resources/collections'));
    }

    public function test_custom_source_directory_is_automatically_added_if_missing()
    {
        rmdir(Hyde::path('foo'));
        $this->assertDirectoryDoesNotExist(Hyde::path('foo'));

        DataCollection::$sourceDirectory = 'foo';
        DataCollection::markdown('bar');

        $this->assertDirectoryExists(Hyde::path('foo'));

        DataCollection::$sourceDirectory = 'resources/collections';
        rmdir(Hyde::path('foo'));
    }
}
