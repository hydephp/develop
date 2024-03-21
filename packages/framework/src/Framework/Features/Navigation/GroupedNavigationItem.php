<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\ExternalRoute;

/**
 * @deprecated Use NavigationGroup instead
 */
class GroupedNavigationItem extends NavigationItem
{
    protected array $items = [];

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
        parent::__construct(null, $label, $priority, static::normalizeGroupKey($label));

        $this->addItems($items);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(NavigationItem $item): static
    {
        $item->group ??= $this->group;

        $this->items[] = $item;

        return $this;
    }

    public function addItems(array $items): static
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

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
