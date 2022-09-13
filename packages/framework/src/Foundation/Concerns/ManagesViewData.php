<?php

namespace Hyde\Framework\Foundation\Concerns;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Facades\View;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Framework\HydeKernel
 */
trait ManagesViewData
{
    public function shareViewData(HydePage $page): void
    {
        View::share('page', $page);
        View::share('currentPage', $page->getRouteKey());
        View::share('currentRoute', $page->getRoute());
    }

    public function currentPage(): ?string
    {
        return View::shared('currentPage');
    }

    public function currentRoute(): ?Route
    {
        return View::shared('currentRoute');
    }
}
