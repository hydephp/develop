<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Hyde\Pages\DocumentationPage;

use function min;
use function collect;

/**
 * Abstraction for a grouped navigation menu item, like a dropdown or a sidebar group.
 */
class NavigationGroup
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavigationItem> */
    protected Collection $items;
    protected string $label;
    protected int $priority;

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
        $this->items = new Collection();
        $this->label = $label;
        $this->priority = $priority;

        $this->add($items);
    }

    public static function create(string $label, array $items = [], int $priority = NavigationMenu::LAST): static
    {
        return new static($label, $items, $priority);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getGroupKey(): string
    {
        return Str::slug($this->label);
    }

    public function getPriority(): int
    {
        if ($this->containsOnlyDocumentationPages()) {
            // For sidebar groups, we use the priority of the lowest priority child, unless the dropdown instance itself has a lower priority.
            return min($this->priority, collect($this->getItems())->min(fn (NavigationItem $item): int => $item->getPriority()));
        }

        return $this->priority;
    }

    /** @return \Illuminate\Support\Collection<\Hyde\Framework\Features\Navigation\NavigationItem> */
    public function getItems(): Collection
    {
        return $this->items->sortBy(fn (NavigationItem|NavigationGroup $item) => $item->getPriority())->values();
    }

    /** @param \Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup|array<\Hyde\Framework\Features\Navigation\NavigationItem|\Hyde\Framework\Features\Navigation\NavigationGroup> $items */
    public function add(NavigationItem|NavigationGroup|array $items): static
    {
        foreach (Arr::wrap($items) as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    protected function addItem(NavigationItem|NavigationGroup $item): void
    {
        $this->items->push($item);
    }

    protected function containsOnlyDocumentationPages(): bool
    {
        return count($this->getItems()) && collect($this->getItems())->every(function (NavigationItem $item): bool {
            return $item->getPage() instanceof DocumentationPage;
        });
    }

    /** @experimental This method is subject to change before its release. */
    public static function normalizeGroupKey(string $group): string
    {
        return Str::slug($group);
    }
}
