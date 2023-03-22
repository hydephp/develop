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
use function in_array;

/**
 * @internal Bootstrap service that loads the YAML configuration file.
 *
 * @see docs/digging-deeper/customization.md#yaml-configuration
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

    /** @return array|array<string, array> */
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
        // (A configuration namespace is defined as the first level in the service container
        // configuration repository array, and usually corresponds 1:1 with a file in the config directory.)
        if ($this->configurationContainsNamespaces($yaml)) {
            foreach ($yaml as $namespace => $data) {
                $this->mergeConfiguration($namespace, $data ?? []);
            }

            return;
        }

        // Otherwise, we can merge using the default strategy, which is simply applying all the data.
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
