<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Support\Paginator;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\TestCase;
use InvalidArgumentException;

/**
 * @covers \Hyde\Support\Paginator
 */
class PaginatorTest extends TestCase
{
    public function testItCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Paginator::class, new Paginator());
    }

    public function testGetPaginatedPageCollection()
    {
        $this->assertEquals(collect([]), (new Paginator())->getPaginatedItems());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $collection = (new Paginator(
            range(1, 50),
        ))->getPaginatedItems();

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
            range(1, 50),
            10)
        )->getPaginatedItems();

        $this->assertCount(5, $collection);
        $this->assertCount(10, $collection->first());
        $this->assertCount(10, $collection->last());
    }

    public function testGetItemsForPageReturnsTheCorrectChunk()
    {
        $paginator = new Paginator(
            range(1, 50),
            10
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
        $service = new Paginator(range(1, 50));
        $service->setCurrentPage(2);
        $this->assertSame(2, $service->currentPage());
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeGreaterThanNought()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new Paginator();
        $service->setCurrentPage(0);
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeGreaterThanNought2()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new Paginator();
        $service->setCurrentPage(-1);
    }

    public function testSetCurrentPageNumberRequiresIntegerToBeLessThanTotalPages()
    {
        $service = new Paginator(
            range(1, 50),
            10
        );

        $service->setCurrentPage(5);
        $this->assertSame(5, $service->currentPage());

        $this->expectException(InvalidArgumentException::class);
        $service->setCurrentPage(6);
    }

    public function testCannotSetInvalidCurrentPageNumberInConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        new Paginator(
            range(1, 50),
            10,
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

    public function testHasMultiplePagesReturnsTrueIfThereAreEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertTrue($this->makePaginator()->hasMultiplePages());
    }

    public function testHasPagesReturnsFalseIfThereAreNotEnoughItemsToSplitIntoMultiplePages()
    {
        $this->assertFalse($this->makePaginator(1, 9)->canNavigateForward());
    }

    public function testHasMorePagesReturnsTrueIfCursorCanNavigateForward()
    {
        $this->assertTrue($this->makePaginator()->canNavigateForward());
    }

    public function testHasMorePagesReturnsFalseIfCursorCannotNavigateForward()
    {
        $this->assertFalse($this->makePaginator()->setCurrentPage(5)->canNavigateForward());
    }

    public function testHasFewerPagesReturnsTrueIfCursorCanNavigateBack()
    {
        $this->assertTrue($this->makePaginator()->setCurrentPage(2)->canNavigateBack());
    }

    public function testHasFewerPagesReturnsFalseIfCursorCannotNavigateBack()
    {
        $this->assertFalse($this->makePaginator()->canNavigateBack());
    }

    public function testPreviousMethodWithoutFewerPagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->previous());
    }

    public function testNextMethodWithoutMorePagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->setCurrentPage(5)->next());
    }

    public function testPreviousMethodReturnsPreviousPageLinkWhenNoBaseRouteIsSet()
    {
        $this->assertSame('page-1.html', $this->makePaginator()->setCurrentPage(2)->previous());
    }

    public function testNextMethodReturnsNextPageLinkWhenNoBaseRouteIsSet()
    {
        $this->assertSame('page-2.html', $this->makePaginator()->setCurrentPage(1)->next());
    }

    public function testPreviousAndNextMethodsWithBaseRouteSet()
    {
        $pages[1] = new InMemoryPage('pages/page-1');
        $pages[2] = new InMemoryPage('pages/page-2');
        $pages[3] = new InMemoryPage('pages/page-3');
        $pages[4] = new InMemoryPage('pages/page-4');
        $pages[5] = new InMemoryPage('pages/page-5');

        foreach ($pages as $page) {
            Hyde::routes()->put($page->getRouteKey(), $page->getRoute());
        }

        $paginator = new Paginator($pages, 2, paginationRouteBasename: 'pages');

        $this->assertFalse($paginator->setCurrentPage(1)->previous());
        $this->assertFalse($paginator->setCurrentPage(3)->next());

        $this->assertSame($pages[2]->getRoute(), $paginator->setCurrentPage(1)->next());
        $this->assertSame($pages[3]->getRoute(), $paginator->setCurrentPage(2)->next());

        $this->assertSame($pages[2]->getRoute(), $paginator->setCurrentPage(3)->previous());
        $this->assertSame($pages[1]->getRoute(), $paginator->setCurrentPage(2)->previous());
    }

    public function testPreviousNumberWithoutFewerPagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->previousPageNumber());
    }

    public function testNextNumberWithoutMorePagesReturnsFalse()
    {
        $this->assertFalse($this->makePaginator()->setCurrentPage(5)->nextPageNumber());
    }

    public function testPreviousNumberReturnsThePreviousPageNumberWhenThereIsOne()
    {
        $this->assertSame(1, $this->makePaginator()->setCurrentPage(2)->previousPageNumber());
    }

    public function testNextNumberReturnsTheNextPageNumberWhenThereIsOne()
    {
        $this->assertSame(2, $this->makePaginator()->setCurrentPage(1)->nextPageNumber());
    }

    public function testGetPageLinks()
    {
        $this->assertSame(
            [
                1 => 'page-1.html',
                2 => 'page-2.html',
                3 => 'page-3.html',
                4 => 'page-4.html',
                5 => 'page-5.html',
            ],
            $this->makePaginator()->getPageLinks()
        );
    }

    public function testGetPageLinksWithBaseRoute()
    {
        $pages[1] = new InMemoryPage('pages/page-1');
        $pages[2] = new InMemoryPage('pages/page-2');
        $pages[3] = new InMemoryPage('pages/page-3');
        $pages[4] = new InMemoryPage('pages/page-4');
        $pages[5] = new InMemoryPage('pages/page-5');

        foreach ($pages as $page) {
            Hyde::routes()->put($page->getRouteKey(), $page->getRoute());
        }

        $paginator = new Paginator($pages, 2, paginationRouteBasename: 'pages');
        $this->assertSame(
            [
                1 => $pages[1]->getRoute(),
                2 => $pages[2]->getRoute(),
                3 => $pages[3]->getRoute(),
            ],
            $paginator->getPageLinks()
        );
    }

    public function testFirstItemNumberOnPage()
    {
        $paginator = $this->makePaginator();
        $this->assertSame(1, $paginator->firstItemNumberOnPage());
        $this->assertSame(11, $paginator->setCurrentPage(2)->firstItemNumberOnPage());
        $this->assertSame(21, $paginator->setCurrentPage(3)->firstItemNumberOnPage());
        $this->assertSame(31, $paginator->setCurrentPage(4)->firstItemNumberOnPage());
        $this->assertSame(41, $paginator->setCurrentPage(5)->firstItemNumberOnPage());

        $paginator = $this->makePaginator(1, 100, 25);
        $this->assertSame(1, $paginator->firstItemNumberOnPage());
        $this->assertSame(26, $paginator->setCurrentPage(2)->firstItemNumberOnPage());
        $this->assertSame(51, $paginator->setCurrentPage(3)->firstItemNumberOnPage());
        $this->assertSame(76, $paginator->setCurrentPage(4)->firstItemNumberOnPage());
    }

    protected function makePaginator(int $start = 1, int $end = 50, int $pageSize = 10): Paginator
    {
        return new Paginator(range($start, $end), $pageSize);
    }
}
