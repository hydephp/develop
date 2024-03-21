<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

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
}
