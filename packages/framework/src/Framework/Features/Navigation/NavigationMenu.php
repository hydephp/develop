<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

class NavigationMenu
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    public function __construct(Arrayable|array $items = [])
    {
        $this->items = new Collection($items);
    }

    /** @return \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    public function getItems(): Collection
    {
        return $this->items;
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
