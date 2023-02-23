<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationsExtension;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\PublicationsExtension
 */
class PublicationsExtensionTest extends TestCase
{
    public function test_get_page_classes_method()
    {
        $this->assertSame([
            PublicationPage::class,
            PublicationListPage::class,
        ], PublicationsExtension::getPageClasses());
    }

    public function test_publication_pages_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertCount(4, $booted->getPages()); // Default pages + publication index + publication page
        $this->assertInstanceOf(PublicationPage::class, $booted->getPages()->get('publication/foo.md'));
    }

    public function test_listing_pages_for_publications_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            PublicationListPage::class,
            $booted->getPage('publication/index')
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
