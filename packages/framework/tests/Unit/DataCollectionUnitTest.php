<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\DataCollections\DataCollection;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;

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

    public function testClassHasStaticSourceDirectoryProperty()
    {
        $this->assertSame('resources/collections', DataCollection::$sourceDirectory);
    }

    public function testConstructorCreatesNewDataCollectionInstance()
    {
        $class = new DataCollection('foo');
        $this->assertInstanceOf(DataCollection::class, $class);
        $this->assertInstanceOf(Collection::class, $class);
    }

    public function testConstructorSetsKey()
    {
        $class = new DataCollection('foo');
        $this->assertSame('foo', $class->key);
    }

    public function testGetCollectionMethodReturnsTheCollectionInstance()
    {
        $class = new DataCollection('foo');
        $this->assertSame($class, $class->getCollection());
    }

    public function testCanConvertCollectionToArray()
    {
        $this->assertSame([], (new DataCollection('foo'))->toArray());
    }

    public function testCanConvertCollectionToJson()
    {
        $this->assertSame('[]', (new DataCollection('foo'))->toJson());
    }

    public function testCanConvertCollectionToArrayWithItems()
    {
        // TODO
    }

    public function testCanConvertCollectionToJsonWithItems()
    {
        // TODO
    }

    public function testFindMarkdownFilesCallsProperGlobPattern()
    {
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('glob')
            ->with(Hyde::path('resources/collections/foo/*.md'), 0)
            ->once();

        app()->instance(Filesystem::class, $filesystem);

        (new DataCollection('foo'))->findMarkdownFiles();

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
    }

    public function testFindMarkdownFilesWithNoFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'glob' => [],
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $class = new DataCollection('foo');
        $this->assertSame([], $class->findMarkdownFiles());

        Mockery::close();
    }

    public function testFindMarkdownFilesWithFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'glob' => ['bar.md'],
            'get' => 'foo',
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $class = new DataCollection('foo');
        $this->assertSame(['bar.md'], $class->findMarkdownFiles());

        Mockery::close();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollection::class, DataCollection::markdown('foo'));
    }
}
