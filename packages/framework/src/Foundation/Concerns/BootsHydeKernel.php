<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait BootsHydeKernel
{
    private bool $readyToBoot = false;
    private bool $booting = false;

    /**
     * The array of booting callbacks.
     *
     * @todo Cherry pick to master
     *
     * @var callable[]
     */
    protected array $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @todo Cherry pick to master
     *
     * @var callable[]
     */
    protected array $bootedCallbacks = [];

    public function boot(): void
    {
        if (! $this->readyToBoot || $this->booting) {
            return;
        }

        $this->booting = true;

        foreach ($this->bootingCallbacks as $callback) {
            $callback($this);
        }

        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);

        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }

        $this->booting = false;
        $this->booted = true;
    }

    /**
     * Register a new boot listener.
     *
     * @todo Cherry pick to master
     *
     * @param  callable  $callback
     * @return void
     */
    public function booting($callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @todo Cherry pick to master
     *
     * @param  callable  $callback
     * @return void
     */
    public function booted($callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->booted) {
            $callback($this);
        }
    }

    /** @internal */
    public function readyToBoot(): void
    {
        // To give package developers ample time to register their services,
        // don't want to boot the kernel until all providers have been registered.

        $this->readyToBoot = true;
    }

    /** @internal */
    public function rebootCollections(): void
    {
        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);
    }
}
