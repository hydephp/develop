<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Models\PublicationType;
use Hyde\Foundation\PageCollection;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
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

        $booted = PageCollection::boot(Hyde::getInstance());

        $this->assertCount(4, $booted->getPages()); // Default pages + publication index + publication page
        $this->assertInstanceOf(PublicationPage::class, $booted->getPages()->get('publication/foo.md'));
    }

    public function test_listing_pages_for_publications_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::boot(Hyde::getInstance());

        $this->assertInstanceOf(
            PublicationListPage::class,
            $booted->getPage('publication/index')
        );
    }

    protected function createPublication(): void
    {
        $this->directory('publication');

        (new PublicationType('publication'))->save();
        (new PublicationPage('foo', [], '', PublicationType::get('publication')))->save();
    }
}
