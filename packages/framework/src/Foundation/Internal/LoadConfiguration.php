<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Phar;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;

use function array_merge;
use function dirname;
use function is_dir;
use function tap;

/** @internal */
class LoadConfiguration extends BaseLoadConfiguration
{
    /** Get all the configuration files for the application. */
    protected function getConfigurationFiles(Application $app): array
    {
        return tap(parent::getConfigurationFiles($app), function (array &$files) use ($app): void {
            // Inject our custom config file which is stored in `app/config.php`.
            $files['app'] = $app->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config.php';

            $this->providePharSupportIfNeeded($files);
        });
    }

    /** Load the configuration items from all the files. */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository): void
    {
        parent::loadConfigurationFiles($app, $repository);

        $this->mergeConfigurationFiles($repository);

        $this->loadRuntimeConfiguration($app, $repository);
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

    /**
     * Provide support for running Hyde in a Phar archive.
     *
     * @experimental
     *
     * @codeCoverageIgnore
     */
    private static function providePharSupportIfNeeded(array &$files): void
    {
        // If we're running in a Phar and no project config directory exists,
        // we need to adjust the path to use the bundled static Phar config file.

        if (Phar::running() && (! is_dir($files['app']))) {
            $files['app'] = dirname(__DIR__, 6).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
        }
    }

    private function loadRuntimeConfiguration(Application $app, RepositoryContract $repository)
    {
        if ($app->runningInConsole()) {
            //
        }
    }
}
