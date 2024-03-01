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
}
