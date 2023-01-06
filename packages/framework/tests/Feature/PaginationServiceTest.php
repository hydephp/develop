<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use function collect;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PaginationService;
use Hyde\Testing\TestCase;
use function range;

/**
 * @covers \Hyde\Framework\Features\Publications\PaginationService
 */
class PaginationServiceTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PaginationService::class,
            new PaginationService($this->createMock(PublicationType::class))
        );
    }

    public function testGetPaginatedPageCollection()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();

        $this->assertEquals(collect([]), (new PaginationService(PublicationType::get('test-publication')))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();

        $type = PublicationType::get('test-publication');
        foreach (range(1, 50) as $i) {
            $page = new PublicationPage("test-publication-$i", type: $type);
            Hyde::pages()->put($page->getSourcePath(), $page);
        }

        $collection = (new PaginationService(PublicationType::get('test-publication')))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());
    }
}
