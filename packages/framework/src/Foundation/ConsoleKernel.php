<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use LaravelZero\Framework\Kernel;

class ConsoleKernel extends Kernel
{
    /**
     * Get the bootstrap classes for the application.
     */
    protected function bootstrappers(): array
    {
        $bootstrappers = $this->bootstrappers;

        array_splice($bootstrappers, 5, 0,  \Hyde\Foundation\Services\LoadYamlConfiguration::class);

        return $bootstrappers;
    }
}
