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
        $this->assertInstanceOf(PaginationService::class, new PaginationService());
    }

    public function testGetPaginatedPageCollection()
    {
        $this->assertEquals(collect([]), (new PaginationService())->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $collection = (new PaginationService(
            collect(range(1, 50)),
            new PaginationSettings()
        ))->getPaginatedPageCollection();

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
        $collection = (new PaginationService(
            collect(range(1, 50)),
            new PaginationSettings(pageSize: 10))
        )->getPaginatedPageCollection();

        $this->assertCount(5, $collection);
        $this->assertCount(10, $collection->first());
        $this->assertCount(10, $collection->last());
    }

    public function testGetAndSetCurrentPageNumber()
    {
        $service = new PaginationService();

        $this->assertSame(1, $service->currentPage());
        $this->assertSame($service, $service->setCurrentPage(2));
        $this->assertSame(2, $service->currentPage());
    }

    /** Get the page number of the last available page. */
    public function testLastPageReturnsTheLastPageNumber()
    {
        $this->assertSame(5, $this->makeService()->lastPage());
    }

    /** Get the total number of pages. */
    public function testTotalPagesReturnsTheTotalNumberOfPages()
    {
        $this->assertSame(5, $this->makeService()->totalPages());
    }

    /** The number of items to be shown per page. */
    public function testPerPageReturnsTheNumberOfItemsToBeShownPerPage()
    {
        $this->assertSame(10, $this->makeService()->perPage());
    }

    /** Determine the total number of matching items in the data store. */
    public function testTotalReturnsTheTotalNumberOfMatchingItemsInTheDataStore()
    {
        $this->assertSame(50, $this->makeService()->total());
    }

    /** Determine if there are enough items to split into multiple pages. */
    public function testHasPagesReturnsTrueIfThereAreEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertTrue($this->makeService()->hasPages());
    }

    /** Determine if there are more items after the cursor in the data store. */
    public function testHasMorePagesReturnsTrueIfThereAreMoreItemsAfterTheCursorInTheDataStore()
    {
        $this->assertTrue($this->makeService()->hasMorePages());
    }

    /** Determine if there are fewer items after the cursor in the data store. */
    public function testHasFewerPagesReturnsTrueIfThereAreFewerItemsAfterTheCursorInTheDataStore()
    {
        $this->assertFalse($this->makeService()->hasFewerPages());
    }

    protected function makeService(int $start = 1, int $end = 50, int $pageSize = 10): PaginationService
    {
        return new PaginationService(
            collect(range($start, $end)),
            new PaginationSettings(pageSize: $pageSize)
        );
    }
}
