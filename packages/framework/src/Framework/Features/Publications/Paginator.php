<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function collect;
use Hyde\Facades\Route;
use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Hyde\Hyde;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use function is_array;

/**
 * @see \Hyde\Framework\Testing\Feature\PaginationServiceTest
 *
 * @todo Implement sorting from PaginationSettings
 *
 * Internal developer note: It may be useful to match the Laravel method names as much as possible.
 *
 * @link https://laravel.com/docs/9.x/pagination#paginator-instance-methods
 *
 * Additional note: In most cases when this class uses the term "page" it is referring
 * to the paginated page of publications, not the publication pages themselves.
 * This is for the reason stated above, to match the Laravel method names.
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

    /** Determine the total number of matching items in the data store. */
    public function total(): int
    {
        return $this->chunks->flatten()->count();
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

    public function previous(): null|int|\Hyde\Support\Models\Route
    {
        if (! $this->hasFewerPages()) {
            return null;
        }

        if (! isset($this->paginationRouteBasename)) {
            return $this->previousNumber();
        }

        return Route::get("$this->paginationRouteBasename/page-".$this->currentPage - 1);
    }

    public function next(): null|int|\Hyde\Support\Models\Route
    {
        if (! $this->hasMorePages()) {
            return null;
        }

        if (! isset($this->paginationRouteBasename)) {
            return $this->nextNumber();
        }

        return Route::get("$this->paginationRouteBasename/page-".$this->currentPage + 1);
    }

    public function previousNumber(): bool|int
    {
        if (! $this->hasFewerPages()) {
            return false;
        }

        return $this->currentPage - 1;
    }

    public function nextNumber(): bool|int
    {
        if (! $this->hasMorePages()) {
            return false;
        }

        return $this->currentPage + 1;
    }

    public function getNumbersArray(): array
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

    public function itemsStartNumber(): int
    {
        return (($this->currentPage - 1) * $this->perPage()) + 1;
    }

    protected function getPaginationSettings(array|PaginationSettings $paginationSettings): PaginationSettings
    {
        if (is_array($paginationSettings)) {
            return PaginationSettings::fromArray($paginationSettings);
        }

        return $paginationSettings;
    }
}
