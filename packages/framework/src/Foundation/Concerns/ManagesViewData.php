<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ManagesViewData
{
    /**
     * Share data for the page being rendered.
     */
    public function shareViewData(HydePage $page): void
    {
        Render::setPage($page);
    }

    /**
     * Get the route key for the page being rendered.
     */
    public function currentPage(): ?string
    {
        return Render::getCurrentPage();
    }

    /**
     * Get the route for the page being rendered.
     */
    public function currentRoute(): ?Route
    {
        return Render::getCurrentRoute();
    }
}
