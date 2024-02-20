<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;

use function collect;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Consider making into a service which can create the sidebar as well.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 */
class GeneratesDocumentationSidebarMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    /** @var \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    protected RouteCollection $routes;

    protected function __construct()
    {
        $this->items = new Collection();
        $this->routes = Routes::getRoutes(DocumentationPage::class);
    }

    public static function handle(): DocumentationSidebar
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();

        return new DocumentationSidebar($menu->items);
    }

    protected function generate(): void
    {
        $useGroups = $this->usesSidebarGroups();

        $this->routes->each(function (Route $route) use ($useGroups): void {
            if ($this->canAddRoute($route)) {
                $item = NavItem::fromRoute($route);
                $group = $item->getGroup();

                if ($useGroups) {
                    if (! $group) {
                        $group = 'Other';
                    }

                    $groupItem = $this->items->get(Str::slug($group));

                    if ($groupItem === null) {
                        $groupItem = NavItem::dropdown($group, []);
                    }

                    $groupItem->addChild($item);

                    if (! $this->items->has($groupItem->getIdentifier())) {
                        $this->items->put($groupItem->getIdentifier(), $groupItem);
                    }

                    return;
                }

                $this->items->put($route->getRouteKey(), $item);
            }
        });

        // If there are no pages other than the index page, we add it to the sidebar so that it's not empty
        if ($this->items->count() === 0 && DocumentationPage::home() !== null) {
            $this->items->push(NavItem::fromRoute(DocumentationPage::home()));
        }
    }

    protected function usesSidebarGroups(): bool
    {
        // In order to know if we should use groups in the sidebar,
        // we need to loop through the pages and see if they have a group set

        return $this->routes->first(function (Route $route): bool {
            return filled($route->getPage()->navigationMenuGroup());
        }) !== null;
    }

    protected function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation() && ! $route->is(DocumentationPage::homeRouteName());
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
}
