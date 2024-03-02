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
    /**
     * @deprecated Rename to $items
     *
     * @var array<\Hyde\Framework\Features\Navigation\NavItem>
     */
    protected array $children = [];

    // Todo: Consider putting children before priority as it is more commonly used
    public function __construct(string $label, int $priority = NavigationMenu::LAST, array $children = [])
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
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Check if the NavItem instance has children.
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * Add a navigation item to the children of the navigation item.
     *
     * This will turn the parent item into a dropdown. Its destination will be set to null.
     */
    public function addChild(NavItem $item): static
    {
        $item->group ??= $this->group;

        $this->children[] = $item;
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
            $this->addChild($item);
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
        if ($this->hasChildren() && $this->containsOnlyDocumentationPages()) {
            return min($this->priority, collect($this->getChildren())->min(fn (NavItem $child): int => $child->getPriority()));
        }

        return parent::getPriority();
    }

    protected function containsOnlyDocumentationPages(): bool
    {
        return collect($this->getChildren())->every(function (NavItem $child): bool {
            return (! $child->getRoute() instanceof ExternalRoute) && $child->getRoute()->getPage() instanceof DocumentationPage;
        });
    }
}
