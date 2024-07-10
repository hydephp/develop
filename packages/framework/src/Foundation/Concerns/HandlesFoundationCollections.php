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
    /** @return \Hyde\Foundation\Kernel\FileCollection<string, \Hyde\Support\Filesystem\ProjectFile> */
    public function files(): FileCollection
    {
        $this->needsToBeBooted();

        return $this->files;
    }

    /** @return \Hyde\Foundation\Kernel\PageCollection<string, \Hyde\Pages\Concerns\HydePage> */
    public function pages(): PageCollection
    {
        $this->needsToBeBooted();

        return $this->pages;
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
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
