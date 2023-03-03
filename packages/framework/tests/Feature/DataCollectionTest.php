<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\DataCollections\DataCollection;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\DataCollections\DataCollection
 *
 * @see \Hyde\Framework\Testing\Unit\DataCollectionUnitTest
 */
class DataCollectionTest extends TestCase
{
    public function test_find_markdown_files_method_returns_empty_array_if_the_specified_directory_does_not_exist()
    {
        $class = new DataCollection('foo');
        $this->assertIsArray($this->getTestedFindMarkdownFiles());
        $this->assertEmpty($this->getTestedFindMarkdownFiles());
    }

    public function test_find_markdown_files_method_returns_empty_array_if_no_files_are_found_in_specified_directory()
    {
        $this->directory('resources/collections/foo');

        $class = new DataCollection('foo');
        $this->assertIsArray($this->getTestedFindMarkdownFiles());
        $this->assertEmpty($this->getTestedFindMarkdownFiles());
    }

    public function test_find_markdown_files_method_returns_an_array_of_markdown_files_in_the_specified_directory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertSame([
            'resources/collections/foo/bar.md',
            'resources/collections/foo/foo.md',
        ], $this->getTestedFindMarkdownFiles());
    }

    public function test_find_markdown_files_method_does_not_include_files_in_subdirectories()
    {
        $this->directory('resources/collections/foo');
        $this->directory('resources/collections/foo/bar');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar/bar.md');

        $this->assertSame([
            'resources/collections/foo/foo.md',
        ], $this->getTestedFindMarkdownFiles());
    }

    public function test_find_markdown_files_method_does_not_include_files_with_extensions_other_than_md()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.txt');

        $this->assertSame([
            'resources/collections/foo/foo.md',
        ], $this->getTestedFindMarkdownFiles());
    }

    public function test_find_markdown_files_method_does_not_remove_files_starting_with_an_underscore()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/_foo.md');

        $this->assertSame([
            'resources/collections/foo/_foo.md',
        ], $this->getTestedFindMarkdownFiles());
    }

    public function test_static_markdown_helper_discovers_and_parses_markdown_files_in_the_specified_directory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertEquals([
            'resources/collections/foo/foo.md' => new MarkdownDocument([], ''),
            'resources/collections/foo/bar.md' => new MarkdownDocument([], ''),
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
            'foo/bar/foo.md',
        ], $this->getTestedFindMarkdownFiles('bar'));

        DataCollection::$sourceDirectory = 'resources/collections';
    }

    /** @deprecated temporary during refactor */
    protected function getTestedFindMarkdownFiles($file = 'foo'): array
    {
        return (new class($file) extends DataCollection
        {
            public function _findMarkdownFiles(): array
            {
                return $this->findMarkdownFiles();
            }
        })->_findMarkdownFiles();
    }
}
