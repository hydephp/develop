<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Symfony\Component\Yaml\Yaml;

use function array_keys;
use function file_get_contents;
use function array_merge;
use function file_exists;

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

        if (array_keys($yaml) === ['hyde']) {
            $this->mergeConfiguration('hyde', $yaml['hyde']);

            return;
        }

        $this->mergeUsingDefaultStrategy($yaml);
    }

    protected function mergeUsingDefaultStrategy(array $yaml): void
    {
        $this->mergeConfiguration('hyde', $yaml);
    }

    protected function mergeConfiguration(string $namespace, array $yamlData): void
    {
        Config::set($namespace, array_merge(
            Config::getArray($namespace, []),
            $yamlData
        ));
    }
}
