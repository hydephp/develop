<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing;

use function copy;
use function file_put_contents;
use Hyde\Foundation\PageCollection;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function mkdir;

/**
 * @covers \Hyde\Publications\PublicationsExtension
 */
class PublicationDiscoveryTest extends TestCase
{
    public function test_publication_pages_are_discovered()
    {
        $this->directory('publication');
        $this->createPublication();

        $collection = PageCollection::boot(Hyde::getInstance())->getPages();
        $this->assertCount(4, $collection); // Default pages + publication index + publication page
        $this->assertInstanceOf(PublicationPage::class, $collection->get('publication/foo.md'));
    }

    public function test_listing_pages_for_publications_are_discovered()
    {
        $this->directory('publication');
        $this->createPublication();

        $this->assertInstanceOf(
            PublicationListPage::class,
            PageCollection::boot(Hyde::getInstance())->getPage('publication/index')
        );
    }

    protected function createPublication(): void
    {
        copy(Hyde::path('tests/fixtures/test-publication-schema.json'), Hyde::path('publication/schema.json'));
        file_put_contents(Hyde::path('publication/foo.md'),
            '---
__canonical: canonical
__createdAt: 2022-11-16 11:32:52
foo: bar
---

Hello World!
'
        );
    }
}
