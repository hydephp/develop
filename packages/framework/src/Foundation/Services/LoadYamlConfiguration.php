<?php

declare(strict_types=1);

namespace Hyde\Foundation\Services;

use Hyde\Hyde;
use Hyde\Facades\Config;
use LaravelZero\Framework\Application;
use Symfony\Component\Yaml\Yaml;
use function array_merge;
use function file_exists;
use function file_get_contents;

/**
 * @internal
 *
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationServiceTest
 */
class LoadYamlConfiguration
{
    /**
     * Performs a core task that needs to be performed on
     * early stages of the framework.
     */
    public function bootstrap(Application $app): void
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
        Config::set('hyde', array_merge(
            Config::getArray('hyde', []),
            $this->getYaml()
        ));
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
}
