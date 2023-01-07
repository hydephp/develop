<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Route;
use Hyde\Hyde;
use Hyde\Pages\VirtualPage;
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
        $service = new Paginator(collect(range(1, 50)));
        $service->setCurrentPage(2);
        $this->assertSame(2, $service->currentPage());
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeGreaterThanNought()
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = new Paginator();
        $service->setCurrentPage(0);
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeGreaterThanNought2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = new Paginator();
        $service->setCurrentPage(-1);
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeLessThanTotalPages()
    {
        $service = new Paginator(
            collect(range(1, 50)),
            new PaginationSettings(pageSize: 10)
        );

        $service->setCurrentPage(5);
        $this->assertSame(5, $service->currentPage());

        $this->expectException(\InvalidArgumentException::class);
        $service->setCurrentPage(6);
    }

    public function testCannotSetInvalidCurrentPageNumberInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Paginator(
            collect(range(1, 50)),
            new PaginationSettings(pageSize: 10),
            currentPageNumber: 6
        );
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

    public function testPreviousAndNextMethodsWithBaseRouteSet()
    {
        $pages[1] = new VirtualPage('pages/page-1');
        $pages[2] = new VirtualPage('pages/page-2');
        $pages[3] = new VirtualPage('pages/page-3');
        $pages[4] = new VirtualPage('pages/page-4');
        $pages[5] = new VirtualPage('pages/page-5');

        foreach ($pages as $page) {
            Hyde::routes()->put($page->getRouteKey(), $page->getRoute());
        }

        $paginator = new Paginator($pages, new PaginationSettings(pageSize: 2), paginationRouteBasename: 'pages');

        $this->assertNull($paginator->setCurrentPage(1)->previous());
        $this->assertNull($paginator->setCurrentPage(3)->next());

        $this->assertSame($pages[2]->getRoute(), $paginator->setCurrentPage(1)->next());
        $this->assertSame($pages[3]->getRoute(), $paginator->setCurrentPage(2)->next());

        $this->assertSame($pages[2]->getRoute(), $paginator->setCurrentPage(3)->previous());
        $this->assertSame($pages[1]->getRoute(), $paginator->setCurrentPage(2)->previous());
    }

    public function testPreviousNumberWithoutFewerPagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->previousNumber());
    }

    public function testNextNumberWithoutMorePagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->setCurrentPage(5)->nextNumber());
    }

    public function testPreviousNumberReturnsThePreviousPageNumberWhenThereIsOne()
    {
        $this->assertSame(1, $this->makePaginator()->setCurrentPage(2)->previousNumber());
    }

    public function testNextNumberReturnsTheNextPageNumberWhenThereIsOne()
    {
        $this->assertSame(2, $this->makePaginator()->setCurrentPage(1)->nextNumber());
    }

    protected function makePaginator(int $start = 1, int $end = 50, int $pageSize = 10): Paginator
    {
        return new Paginator(
            collect(range($start, $end)),
            new PaginationSettings(pageSize: $pageSize)
        );
    }
}
