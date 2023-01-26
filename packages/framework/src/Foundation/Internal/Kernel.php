<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/**
 * @internal
 */
class Kernel extends \Illuminate\Foundation\Console\Kernel
{
    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }
}
