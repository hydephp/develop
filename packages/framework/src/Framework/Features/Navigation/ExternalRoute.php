<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Support\Models\Route;
use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Str;

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
    protected string $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;

        parent::__construct(new InMemoryPage('external-link-' .Str::slug($destination)));
    }

    public function getLink(): string
    {
        return $this->destination;
    }
}
