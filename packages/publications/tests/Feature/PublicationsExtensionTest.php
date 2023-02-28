<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationsExtension;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\PublicationsExtension
 */
class PublicationsExtensionTest extends TestCase
{
    public function test_get_page_classes_method()
    {
        $this->assertSame([
            //
        ], PublicationsExtension::getPageClasses());
    }

    public function test_publication_pages_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $pages = $booted->getPages()->keys()->toArray();
        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/foo.md',
            'publication/index',
        ], $pages);

        $this->assertInstanceOf(PublicationPage::class, $booted->getPages()->get('publication/foo.md'));
    }

    public function test_publication_files_are_discovered()
    {
        $this->createPublication();

        $booted = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertCount(3, $booted->getAllSourceFiles()); // Default pages + publication page
        $this->assertInstanceOf(SourceFile::class, $booted->getSourceFiles()->get('publication/foo.md'));

        $this->assertCount(1, $booted->getSourceFiles(PublicationPage::class));
    }

    public function test_publication_media_files_are_discovered()
    {
        $this->directory('_media/publication');
        $this->file('_media/publication/foo.jpg', 'foo');

        $booted = FileCollection::init(Hyde::getInstance())->boot();

        $this->assertCount(2, $booted->getMediaFiles()); // App.css + publication media file
        $this->assertInstanceOf(MediaFile::class, $booted->getMediaFiles()->get('_media/publication/foo.jpg'));
    }

    public function test_publication_routes_are_discovered()
    {
        $this->createPublication();

        $booted = RouteCollection::init(Hyde::getInstance())->boot();

        $this->assertCount(4, $booted->getRoutes()); // Default pages + publication index + publication page
        $this->assertInstanceOf(Route::class, $booted->getRoutes()->get('publication/foo'));
    }

    public function test_listing_pages_for_publications_are_generated()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            PublicationListPage::class,
            $booted->getPage('publication/index')
        );
    }

    public function test_paginated_listing_pages_for_publications_are_generated()
    {
        $this->createPublication();
        (new PublicationType('publication', pageSize: 1))->save();
        (new PublicationPage('bar', [], '', PublicationType::get('publication')))->save();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            PublicationListPage::class,
            $booted->getPage('publication/index')
        );

        $this->assertInstanceOf(
            InMemoryPage::class,
            $booted->getPage('publication/page-1')
        );

        $this->assertInstanceOf(
            InMemoryPage::class,
            $booted->getPage('publication/page-2')
        );
    }

    public function test_publication_tag_pages_are_generated()
    {
        $this->createPublication();

        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            InMemoryPage::class,
            $booted->getPage('tags/index')
        );
    }

    protected function createPublication(): void
    {
        $this->directory('publication');

        (new PublicationType('publication'))->save();
        (new PublicationPage('foo', [], '', PublicationType::get('publication')))->save();
    }
}
