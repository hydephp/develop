<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;

use function getenv;
use function is_array;
use function array_map;
use function is_string;
use function array_keys;
use function array_merge;
use function in_array;
use function tap;
use function array_values;
use function str_ireplace;
use function array_combine;

/** @internal */
class LoadConfiguration extends BaseLoadConfiguration
{
    /** Get all the configuration files for the application. */
    protected function getConfigurationFiles(Application $app): array
    {
        return (array) tap(parent::getConfigurationFiles($app), /** @param array<string, string> $files */ function (array &$files) use ($app): void {
            // Inject our custom config file which is stored in `app/config.php`.
            $files['app'] ??= $app->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config.php';
        });
    }

    /** Load the configuration items from all the files. */
    protected function loadConfigurationFiles(Application $app, Repository $repository): void
    {
        parent::loadConfigurationFiles($app, $repository);

        $this->reevaluateEnvironmentVariables($app->make(EnvDataRepository::class), $repository);
        $this->mergeConfigurationFiles($repository);

        $this->loadRuntimeConfiguration($app, $repository);
    }

    private function reevaluateEnvironmentVariables(EnvDataRepository $env, Repository $config): void
    {
        // We need to reevaluate the environment variables after the configuration files have been loaded,
        // as the environment variables may depend on the configuration values.

        $templates = array_map(fn (string $key): string => '{{ env.'.$key.' }}', array_keys($env->all()));
        $replacements = array_combine($templates, array_values($env->all()));

        // A recursive way to replace all the environment variables in the configuration files.
        // This may be made much more elegantly if we created a DynamicConfigRepository that
        // would make the replacements when getting a value, but for now, this will do.

        $array = $config->all();
        $this->doRecursiveReplacement($array, $replacements);
        $config->set($array);
    }

    private function doRecursiveReplacement(array &$array, array $replacements): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->doRecursiveReplacement($value, $replacements);
                $array[$key] = $value;
            }

            if (is_string($value)) {
                $array[$key] = str_ireplace(array_keys($replacements), array_values($replacements), $value);
            }
        }
    }

    private function mergeConfigurationFiles(Repository $repository): void
    {
        // These files do commonly not need to be customized by the user, so to get them out of the way,
        // we don't include them in the default project install.

        foreach (['view', 'cache', 'commands', 'torchlight'] as $file) {
            $this->mergeConfigurationFile($repository, $file);
        }
    }

    private function mergeConfigurationFile(Repository $repository, string $file): void
    {
        // We of course want the user to be able to customize the config files,
        // if they're present, so we'll merge their changes here.

        $repository->set($file, array_merge(
            (array) require __DIR__."/../../../config/$file.php",
            (array) $repository->get($file, [])
        ));
    }

    private function loadRuntimeConfiguration(Application $app, Repository $repository): void
    {
        if ($app->runningInConsole()) {
            if ($this->getArgv() !== null) {
                $this->mergeCommandLineArguments($repository, '--pretty-urls', 'hyde.pretty_urls', true);
                $this->mergeCommandLineArguments($repository, '--no-api', 'hyde.api_calls', false);
            }

            $this->mergeRealtimeCompilerEnvironment($repository, 'HYDE_SERVER_SAVE_PREVIEW', 'hyde.server.save_preview');
            $this->mergeRealtimeCompilerEnvironment($repository, 'HYDE_SERVER_DASHBOARD', 'hyde.server.dashboard.enabled');
            $this->mergeRealtimeCompilerEnvironment($repository, 'HYDE_PRETTY_URLS', 'hyde.pretty_urls');
            $this->mergeRealtimeCompilerEnvironment($repository, 'HYDE_PLAY_CDN', 'hyde.use_play_cdn');
        }
    }

    private function mergeCommandLineArguments(Repository $repository, string $argumentName, string $configKey, bool $value): void
    {
        if (in_array($argumentName, $this->getArgv(), true)) {
            $repository->set($configKey, $value);
        }
    }

    private function mergeRealtimeCompilerEnvironment(Repository $repository, string $environmentKey, string $configKey): void
    {
        if ($this->getEnv($environmentKey) !== false) {
            $repository->set($configKey, $this->getEnv($environmentKey) === 'enabled');
        }
    }

    protected function getArgv(): ?array
    {
        return $_SERVER['argv'] ?? null;
    }

    protected function getEnv(string $name): string|false|null
    {
        return getenv($name);
    }
}
