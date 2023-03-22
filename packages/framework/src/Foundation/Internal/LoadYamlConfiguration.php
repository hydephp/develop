<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Symfony\Component\Yaml\Yaml;

use function array_key_first;
use function file_get_contents;
use function array_merge;
use function file_exists;

/**
 * @internal Bootstrap service that loads the YAML configuration file.
 *
 * @see docs/digging-deeper/customization.md#yaml-configuration
 *
 * It also supports loading multiple configuration namespaces, where a configuration namespace is defined
 * as the first level in the service container configuration repository array, and usually corresponds
 * one-to-one with a file in the config directory. This feature, by design, requires a top-level
 * configuration entry to be present as 'hyde' in the YAML file. Existing config files
 * will be parsed as normal, but can be migrated by indenting all entries by one
 * level, and adding a top-level 'hyde' key. Then additional namespaces can
 * be added underneath as needed.
 */
class LoadYamlConfiguration
{
    /**
     * Performs a core task that needs to be performed on
     * early stages of the framework.
     */
    public function bootstrap(): void
    {
        if ($this->hasYamlConfigFile()) {
            $this->mergeParsedConfiguration();
        }
    }

    protected function hasYamlConfigFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml'))
            || file_exists(Hyde::path('hyde.yaml'));
    }

    protected function getYaml(): array
    {
        return (array) Yaml::parse(file_get_contents($this->getFile()));
    }

    protected function getFile(): string
    {
        return file_exists(Hyde::path('hyde.yml'))
            ? Hyde::path('hyde.yml')
            : Hyde::path('hyde.yaml');
    }

    protected function mergeParsedConfiguration(): void
    {
        $yaml = $this->getYaml();

        // If the Yaml file contains namespaces, we merge those using more granular logic
        // that only applies the namespace data to each configuration namespace.
        if ($this->configurationContainsNamespaces($yaml)) {
            foreach ($yaml as $namespace => $data) {
                $this->mergeConfiguration($namespace, (array) $data);
            }

            return;
        }

        // Otherwise, we can merge using the default strategy, which is simply applying all the data to the hyde namespace.
        $this->mergeConfiguration('hyde', $yaml);
    }

    protected function mergeConfiguration(string $namespace, array $yamlData): void
    {
        Config::set($namespace, array_merge(
            Config::getArray($namespace, []),
            $yamlData
        ));
    }

    protected function configurationContainsNamespaces(array $yaml): bool
    {
        return array_key_first($yaml) === 'hyde';
    }
}
