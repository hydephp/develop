<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function collect;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use function range;
use function sprintf;

/**
 * @see \Hyde\Framework\Testing\Feature\PaginatorTest
 */
class PaginationService
{
    protected Collection $items;

    protected int $pageSize = 25;
    protected int $currentPage = 1;

    /**
     * Optionally provide a route basename to be used in generating the pagination links.
     */
    protected string $paginationRouteBasename;

    public function __construct(Arrayable|array $items = [], int $pageSize = 25, int $currentPageNumber = null, string $paginationRouteBasename = null)
    {
        $this->pageSize = $pageSize;

        $this->generate(collect($items));

        if ($currentPageNumber) {
            $this->setCurrentPage($currentPageNumber);
        }

        if ($paginationRouteBasename) {
            $this->paginationRouteBasename = $paginationRouteBasename;
        }
    }

    protected function generate(Collection $items): void
    {
        $this->items = $items->chunk($this->perPage());
    }

    /** Set the current page number. */
    public function setCurrentPage(int $currentPage): PaginationService
    {
        $this->validateCurrentPageValue($currentPage);

        $this->currentPage = $currentPage;

        return $this;
    }

    /** Get the current page number (which is used as a cursor). */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /** Get the paginated collection */
    public function getPaginatedCollection(): Collection
    {
        return $this->items;
    }

    public function getItemsForPage(): Collection
    {
        return $this->items->get($this->currentPage - 1, collect());
    }

    public function getPageLinks(): array
    {
        $array = [];
        $pageRange = range(1, $this->totalPages());
        if ($this->paginationRouteBasename) {
            foreach ($pageRange as $number) {
                $array[$number] = Route::getOrFail("$this->paginationRouteBasename/page-$number");
            }
        } else {
            foreach ($pageRange as $number) {
                $array[$number] = Hyde::formatLink("page-$number.html");
            }
        }

        return $array;
    }

    /** Get the page number of the last available page. */
    public function lastPage(): int
    {
        return $this->items->count();
    }

    /** Get the total number of pages. */
    public function totalPages(): int
    {
        return $this->items->count();
    }

    /** The number of items to be shown per page. */
    public function perPage(): int
    {
        return $this->pageSize;
    }

    /** Determine if there are enough items to split into multiple pages. */
    public function hasPages(): bool
    {
        return $this->items->count() > 1;
    }

    /** Determine if there are fewer items after the cursor in the data store. */
    public function canNavigateLeft(): bool
    {
        return $this->currentPage > 1;
    }

    /** Determine if there are more items after the cursor in the data store. */
    public function canNavigateRight(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    public function previousPageNumber(): false|int
    {
        if (! $this->canNavigateLeft()) {
            return false;
        }

        return $this->currentPage - 1;
    }

    public function nextPageNumber(): false|int
    {
        if (! $this->canNavigateRight()) {
            return false;
        }

        return $this->currentPage + 1;
    }

    public function previous(): false|string|Route
    {
        if (! $this->canNavigateLeft()) {
            return false;
        }

        if (! isset($this->paginationRouteBasename)) {
            return Hyde::formatLink($this->formatPageName(-1, true));
        }

        return Route::get("$this->paginationRouteBasename/{$this->formatPageName(-1)}");
    }

    public function next(): false|string|Route
    {
        if (! $this->canNavigateRight()) {
            return false;
        }

        if (! isset($this->paginationRouteBasename)) {
            return Hyde::formatLink($this->formatPageName(+1, true));
        }

        return Route::get("$this->paginationRouteBasename/{$this->formatPageName(+1)}");
    }

    public function firstItemNumberOnPage(): int
    {
        return (($this->currentPage - 1) * $this->perPage()) + 1;
    }

    protected function validateCurrentPageValue(int $currentPage): void
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('Current page number must be greater than 0.');
        }

        if ($currentPage > $this->lastPage()) {
            throw new InvalidArgumentException('Current page number must be less than or equal to the last page number.');
        }
    }

    protected function formatPageName(int $offset, bool $withHtmlExtension = false): string
    {
        return sprintf('page-%d%s', $this->currentPage + $offset, $withHtmlExtension ? '.html' : '');
    }
}
