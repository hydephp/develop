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
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = FileCollection::init(Hyde::getInstance())->boot();
        $this->assertInstanceOf(FileCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
        ], $collection->all());
    }

    public function test_get_file_returns_parsed_file_object_for_given_file_path()
    {
        $this->file('_pages/foo.blade.php');
        $this->assertEquals(new SourceFile('_pages/foo.blade.php', BladePage::class),
            Files::getFile('_pages/foo.blade.php'));
    }

    public function test_get_file_throws_exception_when_file_is_not_found()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [_pages/foo.blade.php] not found');

        Files::getFile('_pages/foo.blade.php');
    }

    public function test_get_source_files_returns_all_discovered_source_files_when_no_parameter_is_supplied()
    {
        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
        ], Files::getFiles()->all());
    }

    public function test_get_source_files_does_not_include_non_page_source_files()
    {
        $this->withoutDefaultPages();
        $this->file('_pages/foo.txt');

        $this->assertEquals([], Files::getFiles()->all());

        $this->restoreDefaultPages();
    }

    public function test_blade_pages_are_discovered()
    {
        $this->file('_pages/foo.blade.php');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.blade.php', BladePage::class), $collection->get('_pages/foo.blade.php'));
    }

    public function test_markdown_pages_are_discovered()
    {
        $this->file('_pages/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_pages/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.md', MarkdownPage::class), $collection->get('_pages/foo.md'));
    }

    public function test_markdown_posts_are_discovered()
    {
        $this->file('_posts/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertArrayHasKey('_posts/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_posts/foo.md', MarkdownPost::class), $collection->get('_posts/foo.md'));
    }

    public function test_documentation_pages_are_discovered()
    {
        $this->file('_docs/foo.md');
        $collection = FileCollection::init(Hyde::getInstance())->boot();
        $this->assertArrayHasKey('_docs/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_docs/foo.md', DocumentationPage::class), $collection->get('_docs/foo.md'));
    }
}
