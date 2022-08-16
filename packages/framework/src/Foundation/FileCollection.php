<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Contracts\HydeKernelContract;
use Illuminate\Support\Collection;

final class FileCollection extends Collection
{
    protected HydeKernelContract $kernel;

    public static function boot(HydeKernelContract $kernel): self
    {
        return (new self())->setKernel($kernel)->discoverFiles();
    }

    protected function __construct($items = [])
    {
        parent::__construct($items);
    }

    protected function setKernel(HydeKernelContract $kernel): self
    {
        $this->kernel = $kernel;

        return $this;
    }
}
