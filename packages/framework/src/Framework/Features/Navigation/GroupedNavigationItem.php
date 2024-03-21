<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\ExternalRoute;

use function min;
use function collect;

/**
 * @deprecated Use NavigationGroup instead
 */
class GroupedNavigationItem extends NavigationItem
{
    /** @var array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    protected array $items = [];

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
        parent::__construct(null, $label, $priority, static::normalizeGroupKey($label));

        $this->addItems($items);
    }

    /**
     * Get the items of the grouped navigation item.
     *
     * For the main navigation menu, this stores any dropdown items.
     *
     * @return array<\Hyde\Framework\Features\Navigation\NavigationItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Add a navigation item to the grouped navigation item.
     */
    public function addItem(NavigationItem $item): static
    {
        $item->group ??= $this->group;

        $this->items[] = $item;

        return $this;
    }

    /**
     * Add multiple navigation items to the grouped navigation item.
     *
     * @param  array<\Hyde\Framework\Features\Navigation\NavigationItem>  $items
     */
    public function addItems(array $items): static
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get the priority to determine the order of the grouped navigation item.
     *
     * For sidebar groups, this is the priority of the lowest priority child, unless the dropdown has a lower priority.
     */
    public function getPriority(): int
    {
        if ($this->containsOnlyDocumentationPages()) {
            return min($this->priority, collect($this->getItems())->min(fn (NavigationItem $child): int => $child->getPriority()));
        }

        return parent::getPriority();
    }

    protected function containsOnlyDocumentationPages(): bool
    {
        if (empty($this->getItems())) {
            return false;
        }

        return collect($this->getItems())->every(function (NavigationItem $child): bool {
            return (! $child->getRoute() instanceof ExternalRoute) && $child->getRoute()->getPage() instanceof DocumentationPage;
        });
    }
}
