<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Facades\Localization;
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
     * Render the given page, within the language context it is compiled for.
     *
     * Everything involved in rendering the page happens within that context, not just the
     * compilation itself, so that view data, metadata, navigation menus, view composers,
     * and extension hooks all resolve their translation strings for the same language.
     */
    public function renderPage(HydePage $page): string
    {
        return Localization::usingLanguage($page->getLanguage(), function () use ($page): string {
            $this->shareViewData($page);

            return $page->compile();
        });
    }

    /**
     * Get the route key for the page being rendered.
     */
    public function currentRouteKey(): ?string
    {
        return Render::getRouteKey();
    }

    /**
     * Get the route for the page being rendered.
     */
    public function currentRoute(): ?Route
    {
        return Render::getRoute();
    }

    /**
     * Get the page being rendered.
     */
    public function currentPage(): ?HydePage
    {
        return Render::getPage();
    }
}
