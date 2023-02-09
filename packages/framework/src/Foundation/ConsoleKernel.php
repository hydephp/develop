<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use LaravelZero\Framework\Kernel;

class ConsoleKernel extends Kernel
{
    /** Get the bootstrap classes for the application. */
    protected function bootstrappers(): array
    {
        // First, we combine the parent bootstrappers into an associative array,
        // so we can easily access them by class name. Then we replace the
        // LoadConfiguration bootstrapper with our own. Finally, we
        // return the bootstrappers without the added keys.

        return array_values(tap(array_combine(parent::bootstrappers(), parent::bootstrappers()), function (array &$array): void {
            $array[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;
        }));
    }
}
