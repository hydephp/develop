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
        $class = new DataCollection();
        $this->assertInstanceOf(DataCollection::class, $class);
        $this->assertInstanceOf(Collection::class, $class);
    }

    public function testCanConvertCollectionToArray()
    {
        $this->assertSame([], (new DataCollection())->toArray());
    }

    public function testCanConvertCollectionToJson()
    {
        $this->assertSame('[]', (new DataCollection())->toJson());
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

        $this->getTestedFindMarkdownFiles();

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
    }

    public function testFindMarkdownFilesWithNoFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'glob' => [],
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame([], $this->getTestedFindMarkdownFiles());

        Mockery::close();
    }

    public function testFindMarkdownFilesWithFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'glob' => ['bar.md'],
            'get' => 'foo',
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame(['bar.md'], $this->getTestedFindMarkdownFiles());

        Mockery::close();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollection::class, DataCollection::markdown('foo'));
    }

    protected function getTestedFindMarkdownFiles(): array
    {
        return array_keys(DataCollection::markdown('foo')->toArray());
    }
}
