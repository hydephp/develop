<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function array_combine;
use function collect;
use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Hyde\Framework\Features\Publications\Paginator;
use Hyde\Testing\TestCase;
use function range;

/**
 * @covers \Hyde\Framework\Features\Publications\Paginator
 */
class PaginatorTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(Paginator::class, new Paginator());
    }

    public function testGetPaginatedPageCollection()
    {
        $this->assertEquals(collect([]), (new Paginator())->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $collection = (new Paginator(
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
        $collection = (new Paginator(
            collect(range(1, 50)),
            new PaginationSettings(pageSize: 10))
        )->getPaginatedPageCollection();

        $this->assertCount(5, $collection);
        $this->assertCount(10, $collection->first());
        $this->assertCount(10, $collection->last());
    }

    public function testGetItemsForPageReturnsTheCorrectChunk()
    {
        $paginator = new Paginator(
            collect(range(1, 50)),
            new PaginationSettings(pageSize: 10)
        );

        $this->assertCount(10, $paginator->setCurrentPage(1)->getItemsForPage());
        $this->assertCount(10, $paginator->setCurrentPage(2)->getItemsForPage());
        $this->assertCount(10, $paginator->setCurrentPage(3)->getItemsForPage());
        $this->assertCount(10, $paginator->setCurrentPage(4)->getItemsForPage());
        $this->assertCount(10, $paginator->setCurrentPage(5)->getItemsForPage());

        $this->assertEquals(range(1, 10), $paginator->setCurrentPage(1)->getItemsForPage()->toArray());
        $this->assertEquals(array_combine(range(10, 19), range(11, 20)), $paginator->setCurrentPage(2)->getItemsForPage()->toArray());
        $this->assertEquals(array_combine(range(20, 29), range(21, 30)), $paginator->setCurrentPage(3)->getItemsForPage()->toArray());
        $this->assertEquals(array_combine(range(30, 39), range(31, 40)), $paginator->setCurrentPage(4)->getItemsForPage()->toArray());
        $this->assertEquals(array_combine(range(40, 49), range(41, 50)), $paginator->setCurrentPage(5)->getItemsForPage()->toArray());
    }

    public function testCanGetCurrentPageNumber()
    {
        $service = new Paginator();
        $this->assertSame(1, $service->currentPage());
    }

    public function testCanSetCurrentPageNumber()
    {
        $service = new Paginator();
        $service->setCurrentPage(2);
        $this->assertSame(2, $service->currentPage());
    }

    public function testLastPageReturnsTheLastPageNumber()
    {
        $this->assertSame(5, $this->makePaginator()->lastPage());
    }

    public function testTotalPagesReturnsTheTotalNumberOfPages()
    {
        $this->assertSame(5, $this->makePaginator()->totalPages());
    }

    public function testPerPageReturnsTheNumberOfItemsToBeShownPerPage()
    {
        $this->assertSame(10, $this->makePaginator()->perPage());
    }

    public function testTotalReturnsTheTotalNumberOfMatchingItemsInTheDataStore()
    {
        $this->assertSame(50, $this->makePaginator()->total());
    }

    public function testHasPagesReturnsTrueIfThereAreEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertTrue($this->makePaginator()->hasPages());
    }

    public function testHasPagesReturnsFalseIfThereAreNotEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertFalse($this->makePaginator(1, 9)->hasMorePages());
    }

    public function testHasMorePagesReturnsTrueIfCursorCanNavigateForward()
    {
        $this->assertTrue($this->makePaginator()->hasMorePages());
    }

    public function testHasMorePagesReturnsFalseIfCursorCannotNavigateForward()
    {
        $this->assertFalse($this->makePaginator()->setCurrentPage(5)->hasMorePages());
    }

    public function testHasFewerPagesReturnsTrueIfCursorCanNavigateBack()
    {
        $this->assertTrue($this->makePaginator()->setCurrentPage(2)->hasFewerPages());
    }

    public function testHasFewerPagesReturnsFalseIfCursorCannotNavigateBack()
    {
        $this->assertFalse($this->makePaginator()->hasFewerPages());
    }

    public function testPreviousMethodWithoutFewerPagesReturnsNull()
    {
        $this->assertNull($this->makePaginator()->previous());
    }

    public function testNextMethodWithoutMorePagesReturnsNull()
    {
        $this->assertNull($this->makePaginator()->setCurrentPage(5)->next());
    }

    public function testPreviousMethodReturnsPreviousPageNumberWhenNoBaseRouteIsSet()
    {
        $this->assertSame(1, $this->makePaginator()->setCurrentPage(2)->previous());
    }

    public function testNextMethodReturnsNextPageNumberWhenNoBaseRouteIsSet()
    {
        $this->assertSame(2, $this->makePaginator()->setCurrentPage(1)->next());
    }

    protected function makePaginator(int $start = 1, int $end = 50, int $pageSize = 10): Paginator
    {
        return new Paginator(
            collect(range($start, $end)),
            new PaginationSettings(pageSize: $pageSize)
        );
    }
}
