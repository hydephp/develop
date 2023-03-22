<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Symfony\Component\Yaml\Yaml;

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

    protected function mergeParsedConfiguration(): void
    {
        $yaml = $this->getYaml();

        $this->mergeUsingDefaultStrategy($yaml);
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

    protected function mergeUsingDefaultStrategy(array $yaml): void
    {
        Config::set('hyde', array_merge(
            Config::getArray('hyde', []),
            $yaml
        ));
    }
}
