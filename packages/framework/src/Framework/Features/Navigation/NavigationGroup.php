<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Hyde\Pages\DocumentationPage;

use function min;
use function collect;

/**
 * Abstraction for a grouped navigation menu item, like a dropdown or a sidebar group.
 */
class NavigationGroup extends NavigationMenu
{
    protected string $label;
    protected int $priority;

    public function __construct(string $label, array $items = [], int $priority = NavigationMenu::LAST)
    {
        $this->label = $label;
        $this->priority = $priority;

        parent::__construct($items);
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
