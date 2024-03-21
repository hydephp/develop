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
        $item->group ??= $this->group;

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
}
