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

    protected bool $generatesSidebar;
    protected bool $usesGroups;

    protected function __construct()
    {
        $this->items = new Collection();

        $this->generatesSidebar = $this instanceof GeneratesDocumentationSidebarMenu;

        $this->routes = $this->generatesSidebar
            ? Routes::getRoutes(DocumentationPage::class)
            : Routes::all();

        $this->usesGroups = $this->usesGroups();
    }

    public static function handle(): NavigationMenu
    {
        $menu = new static();

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
        return $route->getPage()->showInNavigation();
    }

    protected function canGroupRoute(Route $route): bool
    {
        return $this->usesGroups;
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
        $identifier = Str::slug($groupName);
        $group = $this->items->get($identifier);

        return $group ?? $this->createGroupItem($identifier, $groupName);
    }

    protected function createGroupItem(string $identifier, string $groupName): NavItem
    {
        $label = $this->searchForGroupLabelInConfig($identifier) ?? $groupName;

        $priority = $this->searchForGroupPriorityInConfig($identifier);

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

    protected function searchForGroupPriorityInConfig(string $groupKey): ?int
    {
        $key = $this->generatesSidebar ? 'docs.sidebar_order' : 'hyde.navigation.order';

        return Config::getArray($key, [])[$groupKey] ?? null;
    }
}
