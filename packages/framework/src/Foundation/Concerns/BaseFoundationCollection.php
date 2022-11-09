<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Collection;

/**
 * @internal Base class for the kernel auto-discovery collections.
 *
 * @see \Hyde\Foundation\FileCollection
 * @see \Hyde\Foundation\PageCollection
 * @see \Hyde\Foundation\RouteCollection
 */
abstract class BaseFoundationCollection extends Collection
{
    protected HydeKernel $kernel;

    abstract protected function runDiscovery(): self;

    public static function boot(HydeKernel $kernel): static
    {
        return (new static())->setKernel($kernel)->runDiscovery();
    }

    public function getInstance(): static
    {
        return $this;
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
