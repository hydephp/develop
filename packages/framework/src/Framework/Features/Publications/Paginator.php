<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function collect;
use Hyde\Facades\Route;
use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @see \Hyde\Framework\Testing\Feature\PaginatorTest
 */
class Paginator
{
    protected Collection $chunks;

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
        $this->chunks = $items->chunk($this->perPage());
    }

    public function getPaginatedPageCollection(): Collection
    {
        return $this->chunks;
    }

    public function getItemsForPage(): Collection
    {
        return $this->chunks->get($this->currentPage - 1, collect());
    }

    /** Set the current page number. */
    public function setCurrentPage(int $currentPage): Paginator
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('Current page number must be greater than 0.');
        }

        if ($currentPage > $this->lastPage()) {
            throw new InvalidArgumentException('Current page number must be less than or equal to the last page number.');
        }

        $this->currentPage = $currentPage;

        return $this;
    }

    /** Get the current page number (which is used as a cursor). */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /** Get the page number of the last available page. */
    public function lastPage(): int
    {
        return $this->chunks->count();
    }

    /** Get the total number of pages. */
    public function totalPages(): int
    {
        return $this->chunks->count();
    }

    /** The number of items to be shown per page. */
    public function perPage(): int
    {
        return $this->pageSize;
    }

    /** Determine if there are enough items to split into multiple pages. */
    public function hasPages(): bool
    {
        return $this->chunks->count() > 1;
    }

    /** Determine if there are more items after the cursor in the data store. */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    /** Determine if there are fewer items after the cursor in the data store. */
    public function hasFewerPages(): bool
    {
        return $this->currentPage > 1;
    }

    /** Determine the total number of matching items in the data store. */
    public function itemsTotal(): int
    {
        return $this->chunks->flatten()->count();
    }

    public function previous(): null|int|\Hyde\Support\Models\Route
    {
        if (! $this->hasFewerPages()) {
            return null;
        }

        if (! isset($this->paginationRouteBasename)) {
            return $this->lastPageNumber();
        }

        return Route::get("$this->paginationRouteBasename/page-".$this->currentPage - 1);
    }

    public function next(): null|int|\Hyde\Support\Models\Route
    {
        if (! $this->hasMorePages()) {
            return null;
        }

        if (! isset($this->paginationRouteBasename)) {
            return $this->nextPageNumber();
        }

        return Route::get("$this->paginationRouteBasename/page-".$this->currentPage + 1);
    }

    public function lastPageNumber(): bool|int
    {
        if (! $this->hasFewerPages()) {
            return false;
        }

        return $this->currentPage - 1;
    }

    public function nextPageNumber(): bool|int
    {
        if (! $this->hasMorePages()) {
            return false;
        }

        return $this->currentPage + 1;
    }

    public function getPages(): array
    {
        $array = [];
        $pageRange = range(1, $this->totalPages());
        if ($this->paginationRouteBasename) {
            foreach ($pageRange as $number) {
                $array[$number] = Route::getOrFail("$this->paginationRouteBasename/page-".$number);
            }
        } else {
            foreach ($pageRange as $number) {
                $array[$number] = Hyde::formatLink("page-$number.html");
            }
        }

        return $array;
    }

    public function firstItemNumberOnPage(): int
    {
        return (($this->currentPage - 1) * $this->perPage()) + 1;
    }
}
