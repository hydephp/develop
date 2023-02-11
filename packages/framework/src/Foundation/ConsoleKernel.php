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
        $bootstrappers = $this->bootstrappers;

        return array_merge($bootstrappers, [
            \Hyde\Foundation\Services\LoadYamlConfiguration::class,
        ]);
    }
}
