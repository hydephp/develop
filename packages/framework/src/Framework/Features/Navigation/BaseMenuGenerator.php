<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Illuminate\Support\Str;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;

use function filled;
use function strtolower;

/**
 * @experimental This class may change significantly before its release.
 */
abstract class BaseMenuGenerator
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    /** @var \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    protected RouteCollection $routes;

    /** @var class-string<\Hyde\Framework\Features\Navigation\NavigationMenu> */
    protected string $menuType;

    protected bool $generatesSidebar;
    protected bool $usesGroups;

    /** @param class-string<\Hyde\Framework\Features\Navigation\NavigationMenu> $menuType */
    protected function __construct(string $menuType)
    {
        $this->menuType = $menuType;

        $this->items = new Collection();

        $this->generatesSidebar = $menuType === DocumentationSidebar::class;

        $this->routes = $this->generatesSidebar
            ? Routes::getRoutes(DocumentationPage::class)
            : Routes::all();

        $this->usesGroups = $this->usesGroups();
    }

    public static function handle(): NavigationMenu
    {
        $menu = new static(NavigationMenu::class);

        $menu->generate();

        return new NavigationMenu($menu->items);
    }

    protected function generate(): void
    {
        $this->routes->each(function (Route $route): void {
            if ($this->canAddRoute($route)) {
                if ($this->canGroupRoute($route)) {
                    $this->addRouteToGroup($route);
                } else {
                    $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));
                }
            }
        });
    }

    protected function usesGroups(): bool
    {
        if ($this->generatesSidebar) {
            // In order to know if we should use groups in the sidebar, we need to loop through the pages and see if they have a group set.
            // This automatically enables the sidebar grouping for all pages if at least one group is set.

            return $this->routes->first(fn (Route $route): bool => filled($route->getPage()->navigationMenuGroup())) !== null;
        } else {
            return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
        }
    }

    protected function canAddRoute(Route $route): bool
    {
        if (! $route->getPage()->showInNavigation()) {
            return false;
        }

        if ($this->generatesSidebar) {
            // Since the index page is linked in the header, we don't want it in the sidebar
            return ! $route->is(DocumentationPage::homeRouteName());
        } else {
            // While we for the most part can rely on the navigation visibility state provided by the navigation data factory,
            // we need to make an exception for documentation pages, which generally have a visible state, as the data is
            // also used in the sidebar. But we only want the documentation index page to be in the main navigation.
            return ! $route->getPage() instanceof DocumentationPage || $route->is(DocumentationPage::homeRouteName());
        }
    }

    protected function canGroupRoute(Route $route): bool
    {
        if (! $this->usesGroups) {
            return false;
        }

        return true;
    }

    protected function addRouteToGroup(Route $route): void
    {
        $item = NavItem::fromRoute($route);

        $groupName = $this->generatesSidebar ? ($item->getGroup() ?? 'Other') : $item->getGroup();

        $groupItem = $this->getOrCreateGroupItem($groupName);

        $groupItem->addChild($item);

        if (! $this->items->has($groupItem->getIdentifier())) {
            $this->items->put($groupItem->getIdentifier(), $groupItem);
        }
    }

    protected function getOrCreateGroupItem(string $groupName): NavItem
    {
        $groupKey = Str::slug($groupName);
        $group = $this->items->get($groupKey);

        return $group ?? $this->createGroupItem($groupKey, $groupName);
    }

    protected function createGroupItem(string $groupKey, string $groupName): NavItem
    {
        $label = $this->searchForGroupLabelInConfig($groupKey) ?? $groupName;

        $priority = $this->searchForGroupPriorityInConfig($groupKey);

        return NavItem::dropdown($this->normalizeGroupLabel($label), [], $priority);
    }

    protected function normalizeGroupLabel(string $label): string
    {
        // If there is no label, and the group is a slug, we can make a title from it
        if ($label === strtolower($label)) {
            return Hyde::makeTitle($label);
        }

        return $label;
    }

    protected function searchForGroupLabelInConfig(string $groupKey): ?string
    {
        $key = $this->generatesSidebar ? 'docs.sidebar_group_labels' : 'hyde.navigation.labels';

        return Config::getArray($key, [])[$groupKey] ?? null;
    }

    protected function searchForGroupPriorityInConfig(string $groupKey): ?int
    {
        $key = $this->generatesSidebar ? 'docs.sidebar_order' : 'hyde.navigation.order';

        return Config::getArray($key, [])[$groupKey] ?? null;
    }
}
