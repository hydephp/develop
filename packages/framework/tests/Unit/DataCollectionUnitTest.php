<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Support\DataCollections;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;

/**
 * @covers \Hyde\Support\DataCollections
 *
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollectionUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testClassHasStaticSourceDirectoryProperty()
    {
        $this->assertSame('resources/collections', DataCollections::$sourceDirectory);
    }

    public function testConstructorCreatesNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollections::class, new DataCollections());
    }

    public function testClassExtendsCollectionClass()
    {
        $this->assertInstanceOf(Collection::class, new DataCollections());
    }

    public function testCanConvertCollectionToArray()
    {
        $this->assertSame([], (new DataCollections())->toArray());
    }

    public function testCanConvertCollectionToJson()
    {
        $this->assertSame('[]', (new DataCollections())->toJson());
    }

    public function testFindMarkdownFilesCallsProperGlobPattern()
    {
        $filesystem = Mockery::mock(Filesystem::class, ['exists' => true]);
        $filesystem->shouldReceive('glob')
            ->with(Hyde::path('resources/collections/foo/*.{md}'), GLOB_BRACE)
            ->once();

        app()->instance(Filesystem::class, $filesystem);

        DataCollections::markdown('foo')->keys()->toArray();

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
    }

    public function testFindMarkdownFilesWithNoFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'exists' => true,
            'glob' => [],
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame([], DataCollections::markdown('foo')->keys()->toArray());

        Mockery::close();
    }

    public function testFindMarkdownFilesWithFiles()
    {
        $filesystem = Mockery::mock(Filesystem::class, [
            'exists' => true,
            'glob' => ['bar.md'],
            'get' => 'foo',
        ]);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame(['bar.md'], DataCollections::markdown('foo')->keys()->toArray());

        Mockery::close();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollections::class, DataCollections::markdown('foo'));
    }
}
