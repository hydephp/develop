<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;

use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 */
class GeneratesDocumentationSidebarMenu extends BaseMenuGenerator
{
    public static function handle(): DocumentationSidebar
    {
        $menu = new static(DocumentationSidebar::class);

        $menu->generate();

        return new DocumentationSidebar($menu->items);
    }

    protected function generate(): void
    {
        parent::generate();

        // If there are no pages other than the index page, we add it to the sidebar so that it's not empty
        if ($this->items->count() === 0 && DocumentationPage::home() !== null) {
            $this->items->push(NavItem::fromRoute(DocumentationPage::home()));
        }

        $this->sortSidebarGroupsByLowestPriority();
    }

    protected function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route)
            // Since the index page is linked in the header, we don't want it in the sidebar
            && ! $route->is(DocumentationPage::homeRouteName());
    }

    protected function sortSidebarGroupsByLowestPriority(): void
    {
        // While the items accessor sorts the items upon retrieval,
        // we do an initial sorting here to order any groups.

        $this->items = $this->items->sortBy(function (NavItem $item): int {
            return $item->hasChildren()
                ? $this->getLowestPriorityInGroup($item)
                : $item->getPriority();
        })->values();
    }

    protected function getLowestPriorityInGroup(NavItem $item): int
    {
        return collect($item->getChildren())->min(fn (NavItem $child): int => $child->getPriority());
    }
}
