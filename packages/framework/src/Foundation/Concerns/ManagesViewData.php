<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Models\Support\Route;
use Illuminate\Support\Facades\View;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Framework\HydeKernel
 */
trait ManagesViewData
{
    /**
     * Share data for the page being rendered.
     *
     * @param  \Hyde\Framework\Concerns\HydePage  $page
     */
    public function shareViewData(HydePage $page): void
    {
        View::share('page', $page);
        View::share('currentPage', $page->getRouteKey());
        View::share('currentRoute', $page->getRoute());
    }

    /**
     * Get the route key for the page being rendered.
     *
     * @return string|null
     */
    public function currentPage(): ?string
    {
        return View::shared('currentPage');
    }

    /**
     * Get the route for the page being rendered.
     *
     * @return \Hyde\Framework\Models\Support\Route|null
     */
    public function currentRoute(): ?Route
    {
        return View::shared('currentRoute');
    }
}
