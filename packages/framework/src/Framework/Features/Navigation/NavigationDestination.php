<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Stringable;
use Hyde\Support\Models\Route;

class NavigationDestination implements Stringable
{
    protected Route|string $destination;
}
