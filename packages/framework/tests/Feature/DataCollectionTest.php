<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Support\DataCollections;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Support\DataCollections
 *
 * @see \Hyde\Framework\Testing\Unit\DataCollectionUnitTest
 */
class DataCollectionTest extends TestCase
{
    public function testMarkdownCollections()
    {
        $this->directory('resources/collections/foo');
        $this->markdown('resources/collections/foo/foo.md', 'Hello World', ['title' => 'Foo']);
        $this->file('resources/collections/foo/bar.md');

        $this->assertEquals(new DataCollections([
            'foo/foo.md' => new MarkdownDocument(['title' => 'Foo'], 'Hello World'),
            'foo/bar.md' => new MarkdownDocument([], ''),
        ]), DataCollections::markdown('foo'));
    }

    public function testYamlCollections()
    {
        $this->directory('resources/collections/foo');
        $this->markdown('resources/collections/foo/foo.yaml', matter: ['title' => 'Foo']);
        $this->file('resources/collections/foo/bar.yml');

        $this->assertEquals(new DataCollections([
            'foo/foo.yaml' => new FrontMatter(['title' => 'Foo']),
            'foo/bar.yml' => new FrontMatter([]),
        ]), DataCollections::yaml('foo'));
    }

    public function testJsonCollections()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.json', json_encode(['foo' => 'bar']));
        $this->file('resources/collections/foo/bar.json');

        $this->assertEquals(new DataCollections([
            'foo/foo.json' => (object) ['foo' => 'bar'],
            'foo/bar.json' => null,
        ]), DataCollections::json('foo'));
    }

    public function testJsonCollectionsAsArrays()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.json', json_encode(['foo' => 'bar']));
        $this->file('resources/collections/foo/bar.json');

        $this->assertEquals(new DataCollections([
            'foo/foo.json' => ['foo' => 'bar'],
            'foo/bar.json' => null,
        ]), DataCollections::json('foo', true));
    }

    public function testFindMarkdownFilesMethodReturnsEmptyArrayIfTheSpecifiedDirectoryDoesNotExist()
    {
        $class = new DataCollections();
        $this->assertIsArray(DataCollections::markdown('foo')->keys()->toArray());
        $this->assertEmpty(DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testFindMarkdownFilesMethodReturnsEmptyArrayIfNoFilesAreFoundInSpecifiedDirectory()
    {
        $this->directory('resources/collections/foo');

        $class = new DataCollections();
        $this->assertIsArray(DataCollections::markdown('foo')->keys()->toArray());
        $this->assertEmpty(DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testFindMarkdownFilesMethodReturnsAnArrayOfMarkdownFilesInTheSpecifiedDirectory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertSame([
            'foo/bar.md',
            'foo/foo.md',
        ], DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testFindMarkdownFilesMethodDoesNotIncludeFilesInSubdirectories()
    {
        $this->directory('resources/collections/foo');
        $this->directory('resources/collections/foo/bar');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar/bar.md');

        $this->assertSame([
            'foo/foo.md',
        ], DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testFindMarkdownFilesMethodDoesNotIncludeFilesWithExtensionsOtherThanMd()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.txt');

        $this->assertSame([
            'foo/foo.md',
        ], DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testFindMarkdownFilesMethodDoesNotRemoveFilesStartingWithAnUnderscore()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/_foo.md');

        $this->assertSame([
            'foo/_foo.md',
        ], DataCollections::markdown('foo')->keys()->toArray());
    }

    public function testStaticMarkdownHelperDiscoversAndParsesMarkdownFilesInTheSpecifiedDirectory()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/bar.md');

        $this->assertEquals([
            'foo/foo.md' => new MarkdownDocument([], ''),
            'foo/bar.md' => new MarkdownDocument([], ''),
        ], DataCollections::markdown('foo')->toArray());
    }

    public function testStaticMarkdownHelperDoestNotIgnoreFilesStartingWithAnUnderscore()
    {
        $this->directory('resources/collections/foo');
        $this->file('resources/collections/foo/foo.md');
        $this->file('resources/collections/foo/_bar.md');

        $this->assertCount(2, DataCollections::markdown('foo'));
    }

    public function testSourceDirectoryCanBeChanged()
    {
        DataCollections::$sourceDirectory = 'foo';
        $this->directory('foo/bar');
        $this->file('foo/bar/foo.md');

        $this->assertSame([
            'bar/foo.md',
        ], DataCollections::markdown('bar')->keys()->toArray());

        DataCollections::$sourceDirectory = 'resources/collections';
    }

    public function testSourceDirectoryIsAutomaticallyAddedIfMissing()
    {
        $this->directory('resources/collections');
        File::deleteDirectory(Hyde::path('resources/collections'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/collections'));

        DataCollections::markdown('foo');

        $this->assertDirectoryExists(Hyde::path('resources/collections'));
    }

    public function testCustomSourceDirectoryIsAutomaticallyAddedIfMissing()
    {
        $this->directory('foo');
        File::deleteDirectory(Hyde::path('foo'));

        $this->assertDirectoryDoesNotExist(Hyde::path('foo'));

        DataCollections::$sourceDirectory = 'foo';
        DataCollections::markdown('bar');

        $this->assertDirectoryExists(Hyde::path('foo'));

        DataCollections::$sourceDirectory = 'resources/collections';
    }
}
