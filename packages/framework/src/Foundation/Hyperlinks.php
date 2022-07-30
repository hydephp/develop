<?php

namespace Hyde\Framework\Foundation;

use Hyde\Framework\Contracts\HydeKernelContract;

/**
 * Contains helpers and logic for resolving web paths for compiled files.
 */
class Hyperlinks
{
    protected HydeKernelContract $kernel;

    public function __construct(HydeKernelContract $kernel)
    {
        $this->kernel = $kernel;
    }
}
