<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use LaravelZero\Framework\Kernel;
use Hyde\Foundation\Services\LoadYamlConfiguration;

class ConsoleKernel extends Kernel
{
    /**
     * Get the bootstrap classes for the application.
     */
    protected function bootstrappers(): array
    {
        $bootstrappers = $this->bootstrappers;

        // Insert our bootstrapper between load configuration and register provider bootstrappers.
        array_splice($bootstrappers, 5, 0, LoadYamlConfiguration::class);

        // Since we store our application config in `app/config.php`, we need to replace
        // the default LoadConfiguration bootstrapper class with our implementation.

        // First, we combine the parent bootstrappers into an associative array,
        // so we can easily access them by class name. Then we replace the
        // LoadConfiguration bootstrapper with our own. Finally, we
        // return the bootstrappers without the added keys.

        return array_values(tap(array_combine($bootstrappers, $bootstrappers), function (array &$array): void {
            $array[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;
        }));
    }
}
