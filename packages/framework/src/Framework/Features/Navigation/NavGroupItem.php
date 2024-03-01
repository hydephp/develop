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
}
