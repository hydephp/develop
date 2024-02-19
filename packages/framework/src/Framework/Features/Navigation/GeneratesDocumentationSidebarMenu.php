<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;

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

    protected function __construct()
    {
        $this->items = new Collection();
    }

    public static function handle(): DocumentationSidebar
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();
        $menu->removeDuplicateItems();

        return new DocumentationSidebar($menu->items);
    }

    protected function generate(): void
    {
        $routes = Routes::getRoutes(DocumentationPage::class);

        $groups = $this->findSidebarGroups($routes);

        $routes->each(function (Route $route) use ($groups): void {
            if ($this->canAddRoute($route)) {
                $item = NavItem::fromRoute($route);
                $group = $item->getGroup();

                $this->items->put($route->getRouteKey(), $item);
            }
        });

        // If there are no pages other than the index page, we add it to the sidebar so that it's not empty
        if ($this->items->count() === 0 && DocumentationPage::home() !== null) {
            $this->items->push(NavItem::fromRoute(DocumentationPage::home()));
        }
    }

    /** @experimental Might not actually be needed now that groups default to null */
    protected function findSidebarGroups(RouteCollection $routes): array
    {
        // In order to know if we should use groups in the sidebar,
        // we need to loop through all the pages and see if they have a group set

        $groups = [];

        $routes->each(function (Route $route) use (&$groups): void {
            if ($route->getPage()->data('navigation.group')) {
                $groups[Str::slug($route->getPage()->data('navigation.group'))] = true;
            }
        });

        return array_keys($groups);
    }

    protected function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation() && ! $route->is(DocumentationPage::homeRouteName());
    }

    protected function removeDuplicateItems(): void
    {
        $this->items = $this->items->unique(function (NavItem $item): string {
            // Filter using a combination of the group and label to allow duplicate labels in different groups
            return $item->getGroup().Str::slug($item->getLabel()); // Todo we could use this as the "identifier" for the item, as it uniquely identifies it
        });
    }

    protected function sortByPriority(): void
    {
        $this->items = $this->items->sortBy('priority')->values();
    }
}
