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
        // Since we store our application config in `app/config.php`, we need to replace
        // the default LoadConfiguration bootstrapper class with our implementation.
        // We do this by swapping out the LoadConfiguration class with our own.
        // We also inject our Yaml configuration loading bootstrapper.

        // First, we need to register our Yaml configuration repository,
        // as this code executes before service providers are registered.
        $this->app->singleton(Internal\YamlConfigurationRepository::class);

        return [
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadYamlEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \Hyde\Foundation\Internal\LoadYamlConfiguration::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ];
    }
}
