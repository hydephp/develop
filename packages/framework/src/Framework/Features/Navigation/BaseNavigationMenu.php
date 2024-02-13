<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Collection;

use function collect;

abstract class BaseNavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    public Collection $items;

    final protected function __construct()
    {
        $this->items = new Collection();
    }

    public static function create(): static
    {
        $menu = new static();

        $menu->generate();
        $menu->sortByPriority();
        $menu->removeDuplicateItems();

        return $menu;
    }

    protected function generate(): void
    {
        Routes::each(function (Route $route): void {
            if ($this->canAddRoute($route)) {
                $this->addRoute($route);
            }
        });

        collect(Config::getArray('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });
    }

    protected function canAddRoute(Route $route): bool
    {
        return $route->getPage()->showInNavigation();
    }

    protected function removeDuplicateItems(): void
    {
        $this->items = $this->items->unique(function (NavItem $item): string {
            // Filter using a combination of the group and label to allow duplicate labels in different groups
            return $item->getGroup().$item->label;
        });
    }

    protected function sortByPriority(): void
    {
        $this->items = $this->items->sortBy('priority')->values();
    }

    /** @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Add a route to the navigation menu.
     *
     * If you are calling this method manually, you must do so after the kernel has booted.
     *
     * @param  \Hyde\Support\Models\Route  $route
     * @return $this
     */
    public function addRoute(Route $route): static
    {
        $this->items->put($route->getRouteKey(), NavItem::fromRoute($route));

        return $this;
    }
}
