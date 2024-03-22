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
     *
     * For sidebars this is the category key, for navigation menus this is the dropdown key.
     *
     * When using automatic subdirectory based groups, the subdirectory name is the group key.
     * Otherwise, the group key is a "slugified" version of the group's label.
     */
    public function getGroupKey(): ?string;
}
