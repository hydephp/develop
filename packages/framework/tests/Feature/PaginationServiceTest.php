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

    public function testGetItemsForPageReturnsTheCorrectChunk()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    public function testCanGetCurrentPageNumber()
    {
        $service = new PaginationService();
        $this->assertSame(1, $service->currentPage());
    }

    public function testCanSetCurrentPageNumber()
    {
        $service = new PaginationService();
        $service->setCurrentPage(2);
        $this->assertSame(2, $service->currentPage());
    }

    public function testLastPageReturnsTheLastPageNumber()
    {
        $this->assertSame(5, $this->makeService()->lastPage());
    }

    public function testTotalPagesReturnsTheTotalNumberOfPages()
    {
        $this->assertSame(5, $this->makeService()->totalPages());
    }

    public function testPerPageReturnsTheNumberOfItemsToBeShownPerPage()
    {
        $this->assertSame(10, $this->makeService()->perPage());
    }

    public function testTotalReturnsTheTotalNumberOfMatchingItemsInTheDataStore()
    {
        $this->assertSame(50, $this->makeService()->total());
    }

    public function testHasPagesReturnsTrueIfThereAreEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertTrue($this->makeService()->hasPages());
    }

    public function testHasPagesReturnsFalseIfThereAreNotEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertFalse($this->makeService(1, 9)->hasMorePages());
    }

    public function testHasMorePagesReturnsTrueIfCursorCanNavigateForward()
    {
        $this->assertTrue($this->makeService()->hasMorePages());
    }

    public function testHasMorePagesReturnsFalseIfCursorCannotNavigateForward()
    {
        $this->assertFalse($this->makeService()->setCurrentPage(5)->hasMorePages());
    }

    public function testHasFewerPagesReturnsTrueIfCursorCanNavigateBack()
    {
        $this->assertTrue($this->makeService()->setCurrentPage(2)->hasFewerPages());
    }

    public function testHasFewerPagesReturnsFalseIfCursorCannotNavigateBack()
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
