<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;

use function collect;
use function strtolower;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Consider making into a service which can create the sidebar as well.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 */
class GeneratesDocumentationSidebarMenu extends BaseMenuGenerator
{
    public static function handle(): DocumentationSidebar
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();

        return new DocumentationSidebar($menu->items);
    }

    protected function generate(): void
    {
        parent::generate();

        // If there are no pages other than the index page, we add it to the sidebar so that it's not empty
        if ($this->items->count() === 0 && DocumentationPage::home() !== null) {
            $this->items->push(NavItem::fromRoute(DocumentationPage::home()));
        }
    }

    protected function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route)
            // Since the index page is linked in the header, we don't want it in the sidebar
            && ! $route->is(DocumentationPage::homeRouteName());
    }

    protected function sortByPriority(): void
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

    protected function searchForGroupLabelInConfig(string $identifier): ?string
    {
        return Config::getArray('docs.sidebar_group_labels', [])[$identifier] ?? null;
    }

    /** Todo: Move into shared class */
    protected static function normalizeGroupLabel(string $label): string
    {
        // If there is no label, and the group is a slug, we can make a title from it
        if ($label === strtolower($label)) {
            return Hyde::makeTitle($label);
        }

        return $label;
    }

    /** Todo: Move into shared class */
    protected function searchForGroupPriorityInConfig(string $groupKey): ?int
    {
        return Config::getArray('docs.sidebar_order', [])[$groupKey] ?? null;
    }
}
