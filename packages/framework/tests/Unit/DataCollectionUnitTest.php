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
        $this->mockFilesystemFacade(['shouldReceiveGlob' => true]);

        DataCollections::markdown('foo')->keys()->toArray();

        $this->verifyMockeryExpectations();
    }

    public function testFindMarkdownFilesWithNoFiles()
    {
        $this->mockFilesystemFacade();

        $this->assertSame([], DataCollections::markdown('foo')->keys()->toArray());

        $this->verifyMockeryExpectations();
    }

    public function testFindMarkdownFilesWithFiles()
    {
        $this->mockFilesystemFacade(['glob' => ['bar.md']]);

        $this->assertSame(['bar.md'], DataCollections::markdown('foo')->keys()->toArray());

        $this->verifyMockeryExpectations();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollections::class, DataCollections::markdown('foo'));
    }

    protected function mockFilesystemFacade(array $config = []): void
    {
        $defaults = [
            'exists' => true,
            'glob' => [],
            'get' => 'foo',
        ];

        $config = array_merge($defaults, $config);

        $filesystem = Mockery::mock(Filesystem::class, $config);

        if (isset($config['shouldReceiveGlob'])) {
            $filesystem->shouldReceive('glob')
                ->with(Hyde::path('resources/collections/foo/*.{md}'), GLOB_BRACE)
                ->once()
                ->andReturn($config['glob']);
        }

        app()->instance(Filesystem::class, $filesystem);
    }
}
