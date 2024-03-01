<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

class NavGroupItem extends NavItem
{
    /**
     * @deprecated Rename to $items
     *
     * @var array<\Hyde\Framework\Features\Navigation\NavItem>
     */
    protected array $children = [];

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
}
