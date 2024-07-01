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
        // Since we store our application config in `app/config.php`, we need to replace
        // the default LoadConfiguration bootstrapper class with our implementation.
        // We do this by swapping out the LoadConfiguration class with our own.
        // We also inject our Yaml configuration loading bootstrapper.

        /** @var array<class-string> $bootstrappers */
        $bootstrappers = $this->bootstrappers;

        // First we key the array by the class name so we can easily manipulate it.
        $bootstrappers = array_combine($bootstrappers, $bootstrappers);

        // Remove the Laravel Zero LoadConfiguration bootstrapper
        unset($bootstrappers[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class]);

        // Inject our custom LoadConfiguration bootstrapper
        $bootstrappers[\Hyde\Foundation\Internal\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;

        // Now we return the bootstrappers as a numerically indexed array, like it was before.
        return array_values($bootstrappers);
    }
}
