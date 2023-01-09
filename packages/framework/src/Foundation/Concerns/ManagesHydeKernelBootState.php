<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use BadMethodCallException;
use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\Contracts\DynamicPage;
use InvalidArgumentException;

use function in_array;
use function is_subclass_of;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ManagesHydeKernelBootState
{
    /** @var bool Is the Kernel currently booting? */
    protected bool $booting = false;

    /** @var bool Is the Kernel ready to be booted? */
    protected bool $canBoot = false;

    public function isBooted(): bool
    {
        return $this->booted;
    }

    /** @internal */
    public function readyToBoot(): void
    {
        $this->canBoot = true;
    }

    public function boot(): void
    {
        if (! $this->canBoot) {
            throw new BadMethodCallException('The HydeKernel cannot be booted yet.');
        }

        if ($this->isBooted()) {
            return;
        }

        $this->booting = true;

        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);

        $this->booting = false;
        $this->booted = true;
    }

    /** @internal Reboot the kernel - useful for resetting the application during testing */
    public static function reboot(): void
    {
        $kernel = static::getInstance();

        $kernel->files = FileCollection::boot($kernel);
        $kernel->pages = PageCollection::boot($kernel);
        $kernel->routes = RouteCollection::boot($kernel);
    }
}
