<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
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

    /**
     * @experimental These do not seem to be used outside of tests and may be removed
     *
     * @deprecated If kept, they should be renamed to be more generic.
     */
    public function legacy_hasDropdowns(): bool
    {
        return $this->dropdownsEnabled() && count($this->getDropdowns()) >= 1;
    }

    /**
     * @experimental These do not seem to be used outside of tests and may be removed
     *
     * @deprecated If kept, they should be renamed to be more generic.
     *
     * @return array<string, DropdownNavItem>
     */
    public function legacy_getDropdowns(): array
    {
        return $this->items->filter(function (NavItem $item): bool {
            return $item instanceof DropdownNavItem;
        })->values()->all();
    }

    protected function dropdownsEnabled(): bool
    {
        return Config::getString('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }
}
