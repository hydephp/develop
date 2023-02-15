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
trait BootsHydeKernel
{
    private bool $readyToBoot = false;
    private bool $booting = false;

    /** @var array<callable> */
    protected array $bootingCallbacks = [];

    /** @var array<callable> */
    protected array $bootedCallbacks = [];

    /**
     * Boot the Hyde Kernel and run the Auto-Discovery Process.
     */
    public function boot(): void
    {
        if (! $this->readyToBoot || $this->booting) {
            return;
        }

        $this->booting = true;

        $this->files = FileCollection::init($this);
        $this->pages = PageCollection::init($this);
        $this->routes = RouteCollection::init($this);

        foreach ($this->bootingCallbacks as $callback) {
            $callback($this);
        }

        $this->files->boot();
        $this->pages->boot();
        $this->routes->boot();

        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }

        $this->booting = false;
        $this->booted = true;
    }

    /**
     * Register a new boot listener.
     *
     * Your callback will be called before the kernel is booted.
     * You can use this to register your own routes, pages, etc.
     * The kernel instance will be passed to your callback.
     *
     * @param  callable(\Hyde\Foundation\HydeKernel): void  $callback
     */
    public function booting(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * Your callback will be called after the kernel is booted.
     * You can use this to run any logic after discovery has completed.
     * The kernel instance will be passed to your callback.
     *
     * @param  callable(\Hyde\Foundation\HydeKernel): void  $callback
     */
    public function booted(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;
    }

    /** @internal */
    public function readyToBoot(): void
    {
        // To give package developers ample time to register their services,
        // don't want to boot the kernel until all providers have been registered.

        $this->readyToBoot = true;
    }
}
