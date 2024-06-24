<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Illuminate\Support\Str;
use Hyde\Support\DataCollection;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\MarkdownDocument;

/**
 * @covers \Hyde\Support\DataCollection
 *
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollectionUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    protected function tearDown(): void
    {
        MockableDataCollection::tearDown();

        parent::tearDown();
    }

    public function testClassHasStaticSourceDirectoryProperty()
    {
        $this->assertSame('resources/collections', DataCollection::$sourceDirectory);
    }

    public function testConstructorCreatesNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollection::class, new DataCollection());
    }

    public function testClassExtendsCollectionClass()
    {
        $this->assertInstanceOf(Collection::class, new DataCollection());
    }

    public function testCanConvertCollectionToArray()
    {
        $this->assertSame([], (new DataCollection())->toArray());
    }

    public function testCanConvertCollectionToJson()
    {
        $this->assertSame('[]', (new DataCollection())->toJson());
    }

    public function testFindMarkdownFilesCallsProperGlobPattern()
    {
        $filesystem = Mockery::mock(Filesystem::class, ['exists' => true]);
        $filesystem->shouldReceive('glob')
            ->with(Hyde::path('resources/collections/foo/*.{md}'), GLOB_BRACE)
            ->once();

        app()->instance(Filesystem::class, $filesystem);

        DataCollection::markdown('foo')->keys()->toArray();

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

        $this->assertSame([], DataCollection::markdown('foo')->keys()->toArray());

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

        $this->assertSame(['bar.md'], DataCollection::markdown('foo')->keys()->toArray());

        Mockery::close();
    }

    public function testStaticMarkdownHelperReturnsNewDataCollectionInstance()
    {
        $this->assertInstanceOf(DataCollection::class, DataCollection::markdown('foo'));
    }
}

class MockableDataCollection extends DataCollection
{
    protected static array $mockFiles = [];

    protected static function findFiles(string $name, array|string $extensions): Collection
    {
        return collect(static::$mockFiles)->keys();
    }

    /**
     * @param  array<string, string>  $files  Filename as key, file contents as value.
     */
    public static function mockFiles(array $files): void
    {
        foreach ($files as $file => $contents) {
            assert(is_string($file), 'File name must be a string.');
            assert(is_string($contents), 'File contents must be a string.');
            assert(str_contains($file, '/'), 'File must be in a directory.');
            assert(str_contains($file, '.'), 'File must have an extension.');
        }

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('get')
            ->andReturnUsing(function (string $file) use ($files) {
                $file = unslash(str_replace(Hyde::path(), '', $file));
                $files = collect($files)->mapWithKeys(fn ($contents, $file) => [Str::before(basename($file), '.') => $contents])->all();

                return $files[$file] ?? '';
            });

        app()->instance(Filesystem::class, $filesystem);

        static::$mockFiles = $files;
    }

    public static function tearDown(): void
    {
        static::$mockFiles = [];
    }
}
