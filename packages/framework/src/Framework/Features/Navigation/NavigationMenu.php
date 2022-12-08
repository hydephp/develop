<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use BadMethodCallException;
use function collect;
use function config;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Collection;
use function in_array;

/**
 * @see \Hyde\Framework\Testing\Feature\NavigationMenuTest
 */
class NavigationMenu extends BaseNavigationMenu
{
    /** @var array<string, array<NavItem>> */
    protected array $dropdowns;

    public function generate(): static
    {
        parent::generate();

        if ($this->dropdownsEnabled()) {
            $this->dropdowns = $this->makeDropdowns();
        }

        return $this;
    }

    public function filter(): static
    {
        parent::filter();

        if ($this->dropdownsEnabled()) {
            $this->items = $this->filterDropdownItems();
        }

        return $this;
    }

    protected function filterDropdownItems(): Collection
    {
        $dropdownItems = collect($this->getDropdowns())->flatten()->toArray();

        return $this->items->reject(function (NavItem $item) use ($dropdownItems): bool {
            return in_array($item, $dropdownItems);
        });
    }

    public function hasDropdowns(): bool
    {
        if (! $this->dropdownsEnabled()) {
            return false;
        }

        return count($this->getDropdowns()) >= 1;
    }

    /** @return array<string, array<NavItem>> */
    public function getDropdowns(): array
    {
        if (! $this->dropdownsEnabled()) {
            throw new BadMethodCallException('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');
        }

        return $this->dropdowns;
    }

    protected static function canBeInDropdown(NavItem $item): bool
    {
        if ($item instanceof DropdownNavItem) {
            return false;
        }

        return ($item->getGroup() !== null) && ! in_array($item->route->getPageClass(), [DocumentationPage::class, MarkdownPost::class]);
    }

    protected static function dropdownsEnabled(): bool
    {
        return config('hyde.navigation.subdirectories', 'hidden') === 'dropdown';
    }

    protected function makeDropdowns(): array
    {
        $dropdowns = [];

        /** @var \Hyde\Framework\Features\Navigation\NavItem $item */
        foreach ($this->items as $item) {
            if (! $this->canBeInDropdown($item)) {
                continue;
            }

            $dropdowns[$item->getGroup()][] = $item;
        }

        return $dropdowns;
    }
}
