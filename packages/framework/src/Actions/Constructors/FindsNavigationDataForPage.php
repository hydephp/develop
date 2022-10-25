<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Models\Navigation\NavigationData;

/**
 * Helper for HydePages to discover data used for navigation menus and the documentation sidebar.
 *
 * @internal
 *
 * @see \Hyde\Framework\Testing\Feature\PageModelConstructorsTest
 * @see \Hyde\Framework\Concerns\HydePage
 */
final class FindsNavigationDataForPage
{
    public static function run(HydePage $page): NavigationData
    {
        return (new self($page))->findNavigationForPage();
    }

    protected function __construct(protected HydePage $page)
    {
    }

    private function findNavigationForPage()
    {
    }
}
