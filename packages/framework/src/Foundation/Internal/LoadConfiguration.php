<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;

/** @internal */
class LoadConfiguration extends BaseLoadConfiguration
{
    /** Get all the configuration files for the application. */
    protected function getConfigurationFiles(Application $app): array
    {
        return tap(parent::getConfigurationFiles($app), function (array &$files) use ($app): void {
            // Inject our custom config file which is stored in `app/config.php`.
            $files['app'] = $app->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config.php';
        });
    }

    /** Load the configuration items from all the files. */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository): void
    {
        parent::loadConfigurationFiles($app, $repository);

        $this->mergeConfigurationFiles($repository);
    }

    private function mergeConfigurationFiles(RepositoryContract $repository): void
    {
        // These files do commonly not need to be customized by the user, so to get them out of the way,
        // we don't include them in the default project install.

        foreach (['view', 'cache', 'commands', 'torchlight'] as $file) {
            $this->mergeConfigurationFile($repository, $file);
        }
    }

    private function mergeConfigurationFile(RepositoryContract $repository, string $file): void
    {
        // We of course want the user to be able to customize the config files,
        // if they're present, so we'll merge their changes here.

        $repository->set($file, array_merge(
            require __DIR__."/../../../config/$file.php",
            (array) $repository->get($file, [])
        ));
    }
}
