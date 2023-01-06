<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function array_combine;
use function collect;
use Hyde\Framework\Features\Publications\Models\PaginationSettings;
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
        $this->assertInstanceOf(PaginationService::class, new PaginationService(new PaginationSettings()));
    }

    public function testGetPaginatedPageCollection()
    {
        $this->assertEquals(collect([]), (new PaginationService(new PaginationSettings()))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $collection = (new PaginationService(new PaginationSettings(),
            collect(range(1, 50))))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());

        $this->assertSame([
            range(1, 25),
            array_combine(range(25, 49), range(26, 50)),
        ], $collection->toArray());
    }

    public function testCollectionIsChunkedBySpecifiedSettingValue()
    {
        $collection = (new PaginationService(new PaginationSettings(pageSize: 10),
            collect(range(1, 50))))->getPaginatedPageCollection();

        $this->assertCount(5, $collection);
        $this->assertCount(10, $collection->first());
        $this->assertCount(10, $collection->last());
    }

    public function testGetAndSetCurrentPageNumber()
    {
        $service = new PaginationService(new PaginationSettings());

        $this->assertSame(1, $service->currentPage());
        $this->assertSame($service, $service->setCurrentPage(2));
        $this->assertSame(2, $service->currentPage());
    }
}
