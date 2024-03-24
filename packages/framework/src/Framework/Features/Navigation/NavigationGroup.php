<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Hyde\Pages\DocumentationPage;

use function min;
use function collect;

/**
 * Abstraction for a grouped navigation menu item, like a dropdown or a sidebar group.
 */
class NavigationGroup implements NavigationElement
{
    /** @var array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    protected array $items = [];
    protected string $label;
    protected int $priority;

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
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
            return min($this->priority, collect($this->getItems())->min(fn (NavigationItem $child): int => $child->getPriority()));
        }

        return $this->priority;
    }

    /** @return array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    public function getItems(): array
    {
        return $this->items;
    }

    /** @param  \Hyde\Framework\Features\Navigation\NavigationItem|array<\Hyde\Framework\Features\Navigation\NavigationItem>  $items */
    public function add(NavigationItem|array $items): static
    {
        foreach (Arr::wrap($items) as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    protected function addItem(NavigationItem $item): void
    {
        $this->items[] = $item;
    }

    protected function containsOnlyDocumentationPages(): bool
    {
        return count($this->getItems()) && collect($this->getItems())->every(function (NavigationItem $child): bool {
            return $child->getPage() instanceof DocumentationPage;
        });
    }
}
