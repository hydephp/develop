<?php

namespace Hyde\Framework;

use Hyde\Framework\Contracts\HydeKernelContract;
use Illuminate\Support\Collection;

class PageCollection extends Collection
{
    protected HydeKernelContract $kernel;

    public function __construct(HydeKernelContract $kernel)
    {
        parent::__construct();

        $this->kernel = $kernel;
    }
}
