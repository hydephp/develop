<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use LaravelZero\Framework\Kernel;
use Hyde\Foundation\Internal\LoadYamlConfiguration;

use function array_combine;
use function array_splice;
use function array_values;
use function tap;

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
        // We do this by swapping out the LoadConfiguration class with our own.

        return array_values(tap(array_combine($bootstrappers, $bootstrappers), function (array &$array): void {
            $array[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;
        }));
    }
}
