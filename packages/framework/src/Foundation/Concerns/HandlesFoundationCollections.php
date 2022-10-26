<?php
declare(strict_types=1);

namespace Hyde\Framework\Foundation\Concerns;

use Hyde\Framework\Foundation\FileCollection;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Foundation\RouteCollection;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Framework\HydeKernel
 */
trait HandlesFoundationCollections
{
    public function files(): FileCollection
    {
        $this->needsToBeBooted();

        return $this->files;
    }

    public function pages(): PageCollection
    {
        $this->needsToBeBooted();

        return $this->pages;
    }

    public function routes(): RouteCollection
    {
        $this->needsToBeBooted();

        return $this->routes;
    }

    protected function needsToBeBooted(): void
    {
        if (! $this->booted) {
            $this->boot();
        }
    }
}
