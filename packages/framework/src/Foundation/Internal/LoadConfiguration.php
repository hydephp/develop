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

        //
    }
}
