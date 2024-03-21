<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

class NavigationGroup
{
    /** @var array<\Hyde\Framework\Features\Navigation\NavigationItem> */
    protected array $items = [];
    protected string $label;
    protected int $priority;
}
