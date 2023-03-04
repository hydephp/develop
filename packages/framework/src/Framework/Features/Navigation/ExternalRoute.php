<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Support\Models\Route;

/**
 * External route used by navigation items.
 *
 * They are not present in the kernel route collection as they are not part of the website.
 *
 * @internal
 * @experimental
 */
class ExternalRoute extends Route
{
    public string $destination;

    public function getLink(): string
    {
        return $this->getDestination();
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }
}
