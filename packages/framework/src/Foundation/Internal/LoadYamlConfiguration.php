<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Support\Arr;
use Hyde\Foundation\Application;
use Illuminate\Config\Repository;

use function array_merge;

/**
 * @internal Bootstrap service that loads the YAML configuration file.
 *
 * @see docs/digging-deeper/customization.md#yaml-configuration
 *
 * It also supports loading multiple configuration namespaces, where a configuration namespace is defined
 * as a firs level entry in the service container configuration repository array, and corresponds
 * one-to-one with a file in the config directory, and a root-level key in the YAML file.
 *
 * The namespace feature by design, requires a top-level configuration entry to be present as 'hyde' in the YAML file.
 * Existing config files will be parsed as normal, but can be migrated by indenting all entries by one level,
 * and adding a top-level 'hyde' key. Then additional namespaces can be added underneath as needed.
 */
class LoadYamlConfiguration
{
    protected YamlConfigurationRepository $yaml;
    protected array $config;

    public function bootstrap(Application $app): void
    {
        $this->yaml = $app->make(YamlConfigurationRepository::class);

        if ($this->yaml->hasYamlConfigFile()) {
            tap($app->make('config'), function (Repository $config): void {
                $this->config = $config->all();
                $this->mergeParsedConfiguration();
            })->set($this->config);
        }
    }

    protected function mergeParsedConfiguration(): void
    {
        foreach ($this->yaml->getData() as $namespace => $data) {
            $this->mergeConfiguration($namespace, Arr::undot($data ?: []));
        }
    }

    protected function mergeConfiguration(string $namespace, array $yaml): void
    {
        $this->config[$namespace] = array_merge($this->config[$namespace] ?? [], $yaml);
    }
}
