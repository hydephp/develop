<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/** @deprecated Use the new NavigationMenu class instead */
class MainNavigationMenu extends BaseNavigationMenu
{
    /** @deprecated Temporary method for refactor */
    public static function create(): static
    {
        return new static(GeneratesMainNavigationMenu::handle()->getItems());
    }
}
