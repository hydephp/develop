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

        $siteName = $this->findSiteName();
        if ($siteName !== null) {
            putenv('SITE_NAME='.$siteName);
        }

        return [
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \Hyde\Foundation\Internal\LoadYamlConfiguration::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ];
    }

    protected function findSiteName(): ?string
    {
        // Experimental proof of concept to run before everything is loaded
        $hyde = require $this->app->configPath('hyde.php');
        if (isset($hyde['name']) && is_string($hyde['name']) && $hyde['name'] !== 'HydePHP') {
            return $hyde['name'];
        }

        $yaml = $this->app->make(Internal\YamlConfigurationRepository::class);

        if($yaml->getFilePath()) {
            $data = $yaml->getData();
            if (isset($data['hyde']['name']) && is_string($data['hyde']['name']) && $data['hyde']['name'] !== 'HydePHP') {
                return $data['hyde']['name'];
            }

            if (isset($data['name']) && is_string($data['name']) && $data['name'] !== 'HydePHP') {
                return $data['name'];
            }
        }

        return null;
    }
}
