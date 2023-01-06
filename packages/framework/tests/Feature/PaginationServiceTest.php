<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use function collect;
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
            new PaginationService(new PaginationSettings())
        );
    }

    public function testGetPaginatedPageCollection()
    {
        $this->assertEquals(collect([]), (new PaginationService(new PaginationSettings()))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $collection = (new PaginationService(new PaginationSettings(), collect(range(1, 50))))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());
    }

    public function testGetAndSetCurrentPageNumber()
    {
        $service = new PaginationService(new PaginationSettings());

        $this->assertSame(1, $service->currentPage());
        $this->assertSame($service, $service->setCurrentPage(2));
        $this->assertSame(2, $service->currentPage());
    }
}
