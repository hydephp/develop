<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @experimental This class may change significantly before its release.
 */
class GeneratesMainNavigationMenu
{
    public function execute(): NavigationMenu
    {
        return new NavigationMenu();
    }
}
