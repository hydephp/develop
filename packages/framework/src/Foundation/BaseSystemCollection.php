<?php

namespace Hyde\Framework\Foundation;

use Illuminate\Support\Collection;
use Hyde\Framework\Contracts\HydeKernelContract;

abstract class BaseSystemCollection extends Collection
{
    protected HydeKernelContract $kernel;

    public static function boot(HydeKernelContract $kernel): static
    {
        return (new static())->setKernel($kernel)->runDiscovery();
    }

    protected function __construct($items = [])
    {
        parent::__construct($items);
    }

    protected function setKernel(HydeKernelContract $kernel): static
    {
        $this->kernel = $kernel;
        return $this;
    }
}
