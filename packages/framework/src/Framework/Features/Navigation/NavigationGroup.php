<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\ExternalRoute;

use function min;
use function collect;

class NavigationGroup
{
    /** @var array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    protected array $items = [];
    protected string $label;
    protected int $priority;

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
        $this->label = $label;
        $this->priority = $priority;

        $this->addItems($items);
    }

    public static function create(string $label, array $items = [], int $priority = NavigationMenu::LAST): static
    {
        return new static($label, $items, $priority);
    }

    /** @return array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(NavigationItem $item): static
    {
        /** @deprecated I don't think we necessarily need to care about this */
        $item->setGroup($item->getGroupKey() ?? Str::slug($this->label));

        $this->items[] = $item;

        return $this;
    }

    /** @param  array<\Hyde\Framework\Features\Navigation\NavigationItem>  $items */
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
     * For sidebar groups, this is the priority of the lowest priority child, unless the dropdown itself has a lower priority.
     */
    public function getPriority(): int
    {
        if ($this->containsOnlyDocumentationPages()) {
            return min($this->priority, collect($this->getItems())->min(fn (NavigationItem $child): int => $child->getPriority()));
        }

        return $this->priority;
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
