<?php

declare(strict_types=1);

namespace Hyde\Framework\Foundation\Concerns;

use Hyde\Framework\HydeKernel;
use Illuminate\Support\Collection;

/**
 * @internal Base class for the kernel auto-discovery collections.
 *
 * @see \Hyde\Framework\Foundation\FileCollection
 * @see \Hyde\Framework\Foundation\PageCollection
 * @see \Hyde\Framework\Foundation\RouteCollection
 */
abstract class BaseFoundationCollection extends Collection
{
    protected HydeKernel $kernel;

    abstract protected function runDiscovery(): self;

    public static function boot(HydeKernel $kernel): static
    {
        return (new static())->setKernel($kernel)->runDiscovery();
    }

    protected function __construct($items = [])
    {
        parent::__construct($items);
    }

    protected function setKernel(HydeKernel $kernel): static
    {
        $this->kernel = $kernel;

        return $this;
    }
}
