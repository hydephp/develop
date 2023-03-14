<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Hyde;
use InvalidArgumentException;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

use function collect;
use function sprintf;
use function range;

/**
 */
class Paginator
{
    protected Collection $paginatedItems;

    protected int $pageSize = 25;
    protected int $currentPage = 1;

    /**
     * Optionally provide a route basename to be used in generating the pagination links.
     */
    protected string $routeBasename;

    public function __construct(Arrayable|array $items = [], int $pageSize = 25, int $currentPageNumber = null, string $paginationRouteBasename = null)
    {
        $this->pageSize = $pageSize;

        $this->generate(collect($items));

        if ($currentPageNumber) {
            $this->setCurrentPage($currentPageNumber);
        }

        if ($paginationRouteBasename) {
            $this->routeBasename = $paginationRouteBasename;
        }
    }

    protected function generate(Collection $items): void
    {
        $this->paginatedItems = $items->chunk($this->perPage());
    }

    /** Set the current page number. */
    public function setCurrentPage(int $currentPage): Paginator
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
    public function getPaginatedItems(): Collection
    {
        return $this->paginatedItems;
    }

    public function getItemsForPage(): Collection
    {
        return $this->paginatedItems->get($this->currentPage - 1);
    }

    public function getPageLinks(): array
    {
        $array = [];
        $pageRange = range(1, $this->totalPages());
        if (isset($this->routeBasename)) {
            foreach ($pageRange as $number) {
                $array[$number] = Routes::get("$this->routeBasename/page-$number") ?? Hyde::formatLink("$this->routeBasename/page-$number");
            }
        } else {
            foreach ($pageRange as $number) {
                $array[$number] = Hyde::formatLink("page-$number.html");
            }
        }

        return $array;
    }

    /** The number of items to be shown per page. */
    public function perPage(): int
    {
        return $this->pageSize;
    }

    /** Get the total number of pages. */
    public function totalPages(): int
    {
        return $this->paginatedItems->count();
    }

    /** Determine if there are enough items to split into multiple pages. */
    public function hasMultiplePages(): bool
    {
        return $this->totalPages() > 1;
    }

    /** Get the page number of the last available page. */
    public function lastPage(): int
    {
        return $this->totalPages();
    }

    /** Determine if there are fewer items after the cursor in the data store. */
    public function canNavigateBack(): bool
    {
        return $this->currentPage > $this->firstPage();
    }

    /** Determine if there are more items after the cursor in the data store. */
    public function canNavigateForward(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    public function previousPageNumber(): false|int
    {
        if (! $this->canNavigateBack()) {
            return false;
        }

        return $this->currentPage - 1;
    }

    public function nextPageNumber(): false|int
    {
        if (! $this->canNavigateForward()) {
            return false;
        }

        return $this->currentPage + 1;
    }

    public function previous(): false|string|Route
    {
        if (! $this->canNavigateBack()) {
            return false;
        }

        if (! isset($this->routeBasename)) {
            return $this->formatLink(-1);
        }

        return $this->getRoute(-1);
    }

    public function next(): false|string|Route
    {
        if (! $this->canNavigateForward()) {
            return false;
        }

        if (! isset($this->routeBasename)) {
            return $this->formatLink(+1);
        }

        return $this->getRoute(+1);
    }

    public function firstItemNumberOnPage(): int
    {
        return (($this->currentPage - 1) * $this->perPage()) + 1;
    }

    protected function validateCurrentPageValue(int $currentPage): void
    {
        if ($currentPage < $this->firstPage()) {
            throw new InvalidArgumentException('Current page number must be greater than 0.');
        }

        if ($currentPage > $this->lastPage()) {
            throw new InvalidArgumentException('Current page number must be less than or equal to the last page number.');
        }
    }

    protected function formatPageName(int $offset): string
    {
        return sprintf('page-%d', $this->currentPage + $offset);
    }

    protected function formatLink(int $offset): string
    {
        return Hyde::formatLink("{$this->formatPageName($offset)}.html");
    }

    protected function getRoute(int $offset): Route|string
    {
        return Routes::get("$this->routeBasename/{$this->formatPageName($offset)}") ?? Hyde::formatLink("$this->routeBasename/{$this->formatPageName($offset)}");
    }

    protected function firstPage(): int
    {
        return 1;
    }
}
