<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/**
 * Base class for the high level feature tests for the versioned documentation pages feature.
 *
 * @see \Hyde\Framework\Testing\Feature\DocumentationVersionsTest
 */
abstract class VersionedDocumentationTestCase extends TestCase
{
    protected function enableVersions(): void
    {
        config(['docs.versions' => ['1.x', '2.x']]);
    }

    protected function rediscoverPages(): void
    {
        Hyde::boot();
    }

    protected function sidebar(string $version): DocumentationSidebar
    {
        return app("navigation.sidebar.$version");
    }

    protected function defaultSidebar(): DocumentationSidebar
    {
        return app('navigation.sidebar');
    }

    protected function mainNavigation(): MainNavigationMenu
    {
        return app('navigation.main');
    }

    /** @return array<string> The route keys of the pages linked by the menu items. */
    protected function menuRouteKeys(NavigationMenu $menu): array
    {
        return $menu->getItems()->map(function ($item): ?string {
            return $item instanceof NavigationItem ? $item->getPage()?->getRouteKey() : null;
        })->filter()->sort()->values()->all();
    }
}
