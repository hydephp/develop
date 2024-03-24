<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Support\Contracts\RouteContract;

abstract class BaseRoute implements RouteContract
{
    /**
     * Cast a route object into a string that can be used in a href attribute.
     */
    public function __toString(): string
    {
        return $this->getLink();
    }
}
