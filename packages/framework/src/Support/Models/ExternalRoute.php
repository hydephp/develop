<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

/**
 * A route that leads to an external URI.
 */
class ExternalRoute extends Route
{
    protected string $destination;
}
