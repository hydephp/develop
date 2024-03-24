<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

/**
 * A route that leads to an external URI.
 *
 * @experimental Take caution when using this class, as it may be subject to change.
 */
class ExternalRoute extends BaseRoute
{
    protected string $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    public function getLink(): string
    {
        return $this->destination;
    }

    public function is(Route|RouteKey|string $route): bool
    {
        return $route instanceof ExternalRoute && $route->destination === $this->destination;
    }

    /** @return array{destination: string} */
    public function toArray(): array
    {
        return [
            'destination' => $this->destination,
        ];
    }
}
