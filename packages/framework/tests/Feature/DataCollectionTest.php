<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use ArgumentCountError;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\DataCollections\DataCollection;
use Hyde\Hyde;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Features\DataCollections\DataCollection
 */
class DataCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(Hyde::path('resources/collections'))) {
            mkdir(Hyde::path('resources/collections'));
        }
    }

    public function test_constructor_creates_new_data_collection_instance()
    {
        $class = new DataCollection('foo');
        $this->assertInstanceOf(DataCollection::class, $class);
        $this->assertInstanceOf(Collection::class, $class);
    }

    public function test_constructor_sets_key()
    {
        $class = new DataCollection('foo');
        $this->assertEquals('foo', $class->key);
    }

    public function test_key_is_required()
    {
        $this->expectException(ArgumentCountError::class);
        new DataCollection();
    }

    public function test_get_collection_method_returns_the_collection_instance()
    {
        $class = new DataCollection('foo');
        $this->assertSame($class, $class->getCollection());
    }

    public function test_get_markdown_files_method_returns_empty_array_if_the_specified_directory_does_not_exist()
    {
        $class = new DataCollection('foo');
        $this->assertIsArray($class->getMarkdownFiles());
        $this->assertEmpty($class->getMarkdownFiles());
    }

    public function test_get_markdown_files_method_returns_empty_array_if_no_files_are_found_in_specified_directory()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        $class = new DataCollection('foo');
        $this->assertIsArray($class->getMarkdownFiles());
        $this->assertEmpty($class->getMarkdownFiles());
        rmdir(Hyde::path('resources/collections/foo'));
    }

    public function test_get_markdown_files_method_returns_an_array_of_markdown_files_in_the_specified_directory()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        Filesystem::touch('resources/collections/foo/foo.md');
        Filesystem::touch('resources/collections/foo/bar.md');

        $this->assertEquals([
            Hyde::path('resources/collections/foo/bar.md'),
            Hyde::path('resources/collections/foo/foo.md'),
        ], (new DataCollection('foo'))->getMarkdownFiles());

        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_get_markdown_files_method_does_not_include_files_in_subdirectories()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        mkdir(Hyde::path('resources/collections/foo/bar'));
        Filesystem::touch('resources/collections/foo/foo.md');
        Filesystem::touch('resources/collections/foo/bar/bar.md');
        $this->assertEquals([
            Hyde::path('resources/collections/foo/foo.md'),
        ], (new DataCollection('foo'))->getMarkdownFiles());
        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_get_markdown_files_method_does_not_include_files_with_extensions_other_than_md()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        Filesystem::touch('resources/collections/foo/foo.md');
        Filesystem::touch('resources/collections/foo/bar.txt');
        $this->assertEquals([
            Hyde::path('resources/collections/foo/foo.md'),
        ], (new DataCollection('foo'))->getMarkdownFiles());
        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_get_markdown_files_method_does_not_remove_files_starting_with_an_underscore()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        Filesystem::touch('resources/collections/foo/_foo.md');

        $this->assertEquals([
            Hyde::path('resources/collections/foo/_foo.md'),
        ], (new DataCollection('foo'))->getMarkdownFiles());
        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_static_markdown_helper_returns_new_data_collection_instance()
    {
        $this->assertInstanceOf(DataCollection::class, DataCollection::markdown('foo'));
    }

    public function test_static_markdown_helper_discovers_and_parses_markdown_files_in_the_specified_directory()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        Filesystem::touch('resources/collections/foo/foo.md');
        Filesystem::touch('resources/collections/foo/bar.md');

        $collection = DataCollection::markdown('foo');

        $this->assertContainsOnlyInstancesOf(MarkdownDocument::class, $collection);

        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_static_markdown_helper_doest_not_ignore_files_starting_with_an_underscore()
    {
        mkdir(Hyde::path('resources/collections/foo'));
        Filesystem::touch('resources/collections/foo/foo.md');
        Filesystem::touch('resources/collections/foo/_bar.md');
        $this->assertCount(2, DataCollection::markdown('foo'));
        File::deleteDirectory(Hyde::path('resources/collections/foo'));
    }

    public function test_class_has_static_source_directory_property()
    {
        $this->assertEquals('resources/collections', DataCollection::$sourceDirectory);
    }

    public function test_source_directory_can_be_changed()
    {
        DataCollection::$sourceDirectory = 'foo';
        mkdir(Hyde::path('foo/bar'), recursive: true);
        Filesystem::touch('foo/bar/foo.md');
        $this->assertEquals([
            Hyde::path('foo/bar/foo.md'),
        ], (new DataCollection('bar'))->getMarkdownFiles());
        File::deleteDirectory(Hyde::path('foo'));
    }
}
