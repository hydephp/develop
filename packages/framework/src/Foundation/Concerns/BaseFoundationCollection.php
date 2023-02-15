<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\HydeKernel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Base class for the kernel auto-discovery collections.
 *
 * @see \Hyde\Foundation\Kernel\FileCollection
 * @see \Hyde\Foundation\Kernel\PageCollection
 * @see \Hyde\Foundation\Kernel\RouteCollection
 * @see \Hyde\Framework\Testing\Unit\BaseFoundationCollectionTest
 */
abstract class BaseFoundationCollection extends Collection
{
    protected HydeKernel $kernel;

    abstract protected function runDiscovery(): self;

    abstract protected function runExtensionCallbacks(): self;

    public static function init(HydeKernel $kernel): static
    {
        return (new static())->setKernel($kernel);
    }

    public function boot(): static
    {
        return $this->runDiscovery();
    }

    protected function __construct(array|Arrayable|null $items = [])
    {
        parent::__construct($items);
    }

    /** @return $this */
    protected function setKernel(HydeKernel $kernel): static
    {
        $this->kernel = $kernel;

        return $this;
    }

    /** @return $this */
    public function getInstance(): static
    {
        return $this;
    }
}
