<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

use Stringable;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;

interface RouteContract extends SerializableContract, Stringable
{
    /**
     * Generate a link that resolves to the route destination.
     */
    public function getLink(): string;

    /**
     * Check if the route is the same as the given route.
     */
    public function is(Route|RouteKey|string $route): bool;
}
