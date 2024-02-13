<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Foundation\Kernel\FileCollection
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 * @covers \Hyde\Foundation\Facades\Files
 */
class FileCollectionTest extends TestCase
{
    public function testBootMethodCreatesNewPageCollectionAndDiscoversPagesAutomatically()
    {
        $collection = FileCollection::init(Hyde::getInstance())->boot();
        $this->assertInstanceOf(FileCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
        ], $collection->all());
    }

    public function testGetFileReturnsParsedFileObjectForGivenFilePath()
    {
        $this->file('_pages/foo.blade.php');
        $this->assertEquals(new SourceFile('_pages/foo.blade.php', BladePage::class),
            Files::getFile('_pages/foo.blade.php'));
    }

    public function testGetFileThrowsExceptionWhenFileIsNotFound()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_pages/foo.blade.php] not found');

        Files::getFile('_pages/foo.blade.php');
    }

    public function testGetSourceFilesReturnsAllDiscoveredSourceFilesWhenNoParameterIsSupplied()
    {
        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
        ], Files::getFiles()->all());
    }

    public function testGetSourceFilesDoesNotIncludeNonPageSourceFiles()
    {
        $this->withoutDefaultPages();
        $this->file('_pages/foo.txt');

        $this->assertEquals([], Files::getFiles()->all());

        $this->restoreDefaultPages();
    }

    public function testBladePagesAreDiscovered()
    {
        $this->file('_pages/foo.blade.php');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.blade.php', BladePage::class), $collection->get('_pages/foo.blade.php'));
    }

    public function testMarkdownPagesAreDiscovered()
    {
        $this->file('_pages/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.md', MarkdownPage::class), $collection->get('_pages/foo.md'));
    }

    public function testMarkdownPostsAreDiscovered()
    {
        $this->file('_posts/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_posts/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_posts/foo.md', MarkdownPost::class), $collection->get('_posts/foo.md'));
    }

    public function testDocumentationPagesAreDiscovered()
    {
        $this->file('_docs/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();
        $this->assertArrayHasKey('_docs/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_docs/foo.md', DocumentationPage::class), $collection->get('_docs/foo.md'));
    }
}
