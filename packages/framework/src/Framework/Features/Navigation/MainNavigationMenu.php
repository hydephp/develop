<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use BadMethodCallException;

/** @deprecated Use the new NavigationMenu class instead */
class MainNavigationMenu extends BaseNavigationMenu
{
    /** @deprecated Temporary method for refactor */
    public static function create(): static
    {
        $menu = new static();
        $menu->items = GeneratesMainNavigationMenu::handle()->getItems();

        return $menu;
    }

    public function hasDropdowns(): bool
    {
        return $this->dropdownsEnabled() && count($this->getDropdowns()) >= 1;
    }

    /** @return array<string, DropdownNavItem> */
    public function getDropdowns(): array
    {
        if (! $this->dropdownsEnabled()) {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        return $this->items->filter(function (NavItem $item): bool {
            return $item instanceof DropdownNavItem;
        })->values()->all();
    }

    protected function dropdownsEnabled(): bool
    {
        return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }
}
