<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\PublicationService;
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
        $publicationType = $this->setupPublication();

        $this->assertInstanceOf(PaginationService::class,
            new PaginationService($publicationType->pagination)
        );
    }

    public function testGetPaginatedPageCollection()
    {
        $publicationType = $this->setupPublication();

        $this->assertEquals(collect([]), (new PaginationService($publicationType->pagination))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $publicationType = $this->setupPublication();

        $collection = (new PaginationService($publicationType->pagination, collect(range(1, 50))))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());
    }

    protected function setupPublication(): PublicationType
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        return PublicationType::get('test-publication');
    }
}
