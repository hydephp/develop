<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\ExternalRoute;

use function min;
use function collect;

/**
 * @todo Consider extracting trait for shared code with navigation menu class
 */
class NavGroupItem extends NavItem
{
    /** @var array<\Hyde\Framework\Features\Navigation\NavItem> */
    protected array $items = [];

    public function __construct(string $label, array $children = [], int $priority = NavigationMenu::LAST)
    {
        parent::__construct(null, $label, $priority, static::normalizeGroupKey($label));

        $this->addChildren($children);
    }

    /**
     * Get the children of the navigation item.
     *
     * For the main navigation menu, this stores any dropdown items.
     *
     * @return array<\Hyde\Framework\Features\Navigation\NavItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Add a navigation item to the children of the navigation item.
     *
     * This will turn the parent item into a dropdown. Its destination will be set to null.
     */
    public function addItem(NavItem $item): static
    {
        $item->group ??= $this->group;

        $this->items[] = $item;
        $this->route = null;

        return $this;
    }

    /**
     * Add multiple navigation items to the children of the navigation item.
     *
     * @param  array<\Hyde\Framework\Features\Navigation\NavItem>  $items
     */
    public function addChildren(array $items): static
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get the priority to determine the order of the navigation item.
     *
     * For sidebar groups, this is the priority of the lowest priority child, unless the dropdown has a lower priority.
     */
    public function getPriority(): int
    {
        if ($this->containsOnlyDocumentationPages()) {
            return min($this->priority, collect($this->getItems())->min(fn (NavItem $child): int => $child->getPriority()));
        }

        return parent::getPriority();
    }

    protected function containsOnlyDocumentationPages(): bool
    {
        if (empty($this->getItems())) {
            return false;
        }

        return collect($this->getItems())->every(function (NavItem $child): bool {
            return (! $child->getRoute() instanceof ExternalRoute) && $child->getRoute()->getPage() instanceof DocumentationPage;
        });
    }
}
