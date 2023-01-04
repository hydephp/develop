<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\FileCollection;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades;

/**
 * @covers \Hyde\Foundation\FileCollection
 * @covers \Hyde\Foundation\Concerns\BaseFoundationCollection
 */
class FileCollectionTest extends TestCase
{
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = FileCollection::boot(Hyde::getInstance());
        $this->assertInstanceOf(FileCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
            '_media/app.css' => new MediaFile('_media/app.css'),
        ], $collection->all());
    }

    public function test_get_source_files_returns_all_discovered_source_files_when_no_parameter_is_supplied()
    {
        $collection = FileCollection::boot(Hyde::getInstance());

        $this->assertEquals([
            '_pages/404.blade.php' => new SourceFile('_pages/404.blade.php', BladePage::class),
            '_pages/index.blade.php' => new SourceFile('_pages/index.blade.php', BladePage::class),
        ], $collection->getSourceFiles()->all());
    }

    public function test_get_source_files_does_not_include_non_page_source_files()
    {
        $this->withoutDefaultPages();
        $this->file('_pages/foo.txt');

        $collection = FileCollection::boot(Hyde::getInstance());
        $this->assertEquals([], $collection->getSourceFiles()->all());

        $this->restoreDefaultPages();
    }

    public function test_get_media_files_returns_all_discovered_media_files()
    {
        $collection = FileCollection::boot(Hyde::getInstance());
        $this->assertEquals([
            '_media/app.css' => new MediaFile('_media/app.css'),
        ], $collection->getMediaFiles()->all());
    }

    public function test_get_media_files_does_not_include_non_media_files()
    {
        $this->file('_media/foo.blade.php');
        $collection = FileCollection::boot(Hyde::getInstance());
        $this->assertEquals([
            '_media/app.css' => new MediaFile('_media/app.css'),
        ], $collection->getMediaFiles()->all());
    }

    public function test_blade_pages_are_discovered()
    {
        $this->file('_pages/foo.blade.php');
        $collection = FileCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_pages/foo.blade.php', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.blade.php', BladePage::class), $collection->get('_pages/foo.blade.php'));
    }

    public function test_markdown_pages_are_discovered()
    {
        $this->file('_pages/foo.md');
        $collection = FileCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_pages/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_pages/foo.md', MarkdownPage::class), $collection->get('_pages/foo.md'));
    }

    public function test_markdown_posts_are_discovered()
    {
        $this->file('_posts/foo.md');
        $collection = FileCollection::boot(Hyde::getInstance());

        $this->assertArrayHasKey('_posts/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_posts/foo.md', MarkdownPost::class), $collection->get('_posts/foo.md'));
    }

    public function test_documentation_pages_are_discovered()
    {
        $this->file('_docs/foo.md');
        $collection = FileCollection::boot(Hyde::getInstance());
        $this->assertArrayHasKey('_docs/foo.md', $collection->toArray());
        $this->assertEquals(new SourceFile('_docs/foo.md', DocumentationPage::class), $collection->get('_docs/foo.md'));
    }

    public function test_get_registered_page_classes_method()
    {
        $this->assertSame([], Facades\FileCollection::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_returns_self()
    {
        $this->assertInstanceOf(FileCollection::class, Facades\FileCollection::registerPageClass(TestPageClass::class));
        $this->assertSame(Facades\FileCollection::getInstance(), Facades\FileCollection::registerPageClass(TestPageClass::class));
    }

    public function test_register_page_class_method_adds_specified_class_name_to_index()
    {
        Facades\FileCollection::registerPageClass(TestPageClass::class);
        $this->assertSame([TestPageClass::class], Facades\FileCollection::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_does_not_add_already_added_class_names()
    {
        Facades\FileCollection::registerPageClass(TestPageClass::class);
        Facades\FileCollection::registerPageClass(TestPageClass::class);
        $this->assertSame([TestPageClass::class], Facades\FileCollection::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_only_accepts_instances_of_hyde_page_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must be a subclass of HydePage.');
        Facades\FileCollection::registerPageClass(\stdClass::class);
    }

    public function test_register_page_class_method_throws_exception_when_collection_is_already_booted()
    {
        //
    }
}

abstract class TestPageClass extends HydePage
{
    //
}
