<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
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
