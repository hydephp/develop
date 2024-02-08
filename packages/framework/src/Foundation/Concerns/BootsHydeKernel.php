<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Features\Navigation\NavigationMenu;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait BootsHydeKernel
{
    private bool $booting = false;

    /** @var array<callable> */
    protected array $bootingCallbacks = [];

    /** @var array<callable> */
    protected array $bootedCallbacks = [];

    /**
     * Determine if the Kernel has booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Boot the Hyde Kernel and run the Auto-Discovery Process.
     */
    public function boot(): void
    {
        if ($this->booting) {
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

        $this->navigation = NavigationMenu::create();

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
}
