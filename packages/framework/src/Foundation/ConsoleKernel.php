<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use LaravelZero\Framework\Kernel;

use function array_merge;

class ConsoleKernel extends Kernel
{
    /**
     * Get the bootstrap classes for the application.
     */
    protected function bootstrappers(): array
    {
        return array_merge($this->bootstrappers, [
            \Hyde\Foundation\Services\LoadYamlConfiguration::class,
        ]);
    }
}
