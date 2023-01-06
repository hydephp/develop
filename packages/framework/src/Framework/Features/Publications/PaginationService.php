<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

use function collect;

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
class PaginationService
{
    protected PaginationSettings $paginationSettings;

    protected Collection $chunks;

    public int $currentPage = 1;

    public function __construct(PaginationSettings $paginationSettings, Arrayable|array $items = [])
    {
        $this->paginationSettings = $paginationSettings;

        $this->generate(collect($items));
    }

    protected function generate(Collection $items): void
    {
        $this->chunks = $items->chunk($this->paginationSettings->pageSize);
    }

    public function getPaginatedPageCollection(): Collection
    {
        return $this->chunks;
    }

    /** Get the current page number. */
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
    public function pagesTotal(): int
    {
        return $this->chunks->count();
    }

    /** The number of items to be shown per page. */
    public function perPage(): int
    {
        return $this->paginationSettings->pageSize;
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

    /** Determine if there are more items in the data store. */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }

    /** Determine if there are fewer items in the data store. */
    public function hasFewerPages(): bool
    {
        return $this->currentPage > 1;
    }
}
