<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use ArgumentCountError;
use Hyde\Framework\Features\DataCollections\DataCollection;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Features\DataCollections\DataCollection
 *
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollectionUnitTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
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

    public function test_static_markdown_helper_returns_new_data_collection_instance()
    {
        $this->assertInstanceOf(DataCollection::class, DataCollection::markdown('foo'));
    }
}
