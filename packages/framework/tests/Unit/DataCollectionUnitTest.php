<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\Internal\FileFinder;
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

    public function testFindMarkdownFilesWithNoFiles()
    {
        $this->mockFileFinder([]);

        $this->assertSame([], DataCollections::markdown('foo')->keys()->toArray());

        $this->verifyMockeryExpectations();
    }

    public function testFindMarkdownFilesWithFiles()
    {
        $this->mockFileFinder(['bar.md']);

        $this->assertSame(['bar.md'], DataCollections::markdown('foo')->keys()->toArray());

        $this->verifyMockeryExpectations();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollections::class, DataCollections::markdown('foo'));
    }

    protected function mockFileFinder(array $files): void
    {
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true);
        $filesystem->shouldReceive('get')->andReturn('foo');

        app()->instance(Filesystem::class, $filesystem);

        $finder = Mockery::mock(FileFinder::class);
        $finder->shouldReceive('handle')->andReturn(collect($files));

        app()->instance(FileFinder::class, $finder);
    }

    protected function verifyMockeryExpectations(): void
    {
        parent::verifyMockeryExpectations();

        app()->forgetInstance(Filesystem::class);
        app()->forgetInstance(FileFinder::class);
    }
}
