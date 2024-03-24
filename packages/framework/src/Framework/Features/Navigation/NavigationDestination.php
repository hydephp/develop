<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Stringable;
use Hyde\Support\Models\Route;

class NavigationDestination implements Stringable
{
    protected Route|string $destination;

    public function __construct(Route|string $destination)
    {
        $this->destination = $destination;
    }

    public function __toString(): string
    {
        return (string) $this->destination;
    }
}
