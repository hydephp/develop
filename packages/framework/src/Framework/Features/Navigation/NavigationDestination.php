<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Stringable;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;

use function is_string;

class NavigationDestination implements Stringable
{
    protected Route|string $destination;

    public function __construct(Route|string $destination)
    {
        if (is_string($destination) && Routes::has($destination)) {
            $destination = Routes::get($destination);
        }

        $this->destination = $destination;
    }

    public function __toString(): string
    {
        return $this->getLink();
    }

    public function getLink(): string
    {
        return (string) $this->destination;
    }

    /** @experimental */
    public function getRoute(): ?Route
    {
        return $this->destination instanceof Route ? $this->destination : null;
    }
}
