<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Facades\Route;
use Hyde\Foundation\PageCollection;
use function collect;
use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
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
class PaginationService
{
    protected PaginationSettings $paginationSettings;

    protected Collection $chunks;

    public int $currentPage = 1;

    /**
     * Optionally provide a collection of the page listing models to use in pagination links.
     *
     * @var \Hyde\Foundation\PageCollection<\Hyde\Pages\VirtualPage>
     */
    protected PageCollection $pageCollection;

    public function __construct(Arrayable|array $items = [], PaginationSettings|array $paginationSettings = [], PageCollection $pageCollection = null)
    {
        $this->paginationSettings = $this->getPaginationSettings($paginationSettings);

        $this->generate(collect($items));

        if ($pageCollection) {
            $this->pageCollection = $pageCollection;
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

    /** Set the current page number. */
    public function setCurrentPage(int $currentPage): PaginationService
    {
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

    public function previous(): ?\Hyde\Support\Models\Route
    {
        if (! $this->hasFewerPages()) {
            return null;
        }

        $routeBaseName = 'test'; // FIXME
        return Route::get("$routeBaseName/page-" . $this->currentPage - 1);
    }

    public function next(): ?\Hyde\Support\Models\Route
    {
        if (! $this->hasMorePages()) {
            return null;
        }

        $routeBaseName = 'test';
        return Route::get("$routeBaseName/page-" . $this->currentPage + 1);
    }

    protected function getPaginationSettings(array|PaginationSettings $paginationSettings): PaginationSettings
    {
        if (is_array($paginationSettings)) {
            return PaginationSettings::fromArray($paginationSettings);
        }

        return $paginationSettings;
    }
}
