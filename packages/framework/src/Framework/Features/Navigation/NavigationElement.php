<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @deprecated
 */
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
}
