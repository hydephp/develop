<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

interface NavigationElement
{
    /**
     * Get the label of the navigation item.
     */
    public function getLabel(): string;

    /**
     * Get the priority to determine the order of the navigation item.
     */
    public function getPriority(): int;

    /**
     * Get the group identifier key of the navigation item, if any.
     */
    public function getGroupKey(): ?string;
}
