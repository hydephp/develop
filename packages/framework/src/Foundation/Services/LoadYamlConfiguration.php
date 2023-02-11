<?php

declare(strict_types=1);

namespace Hyde\Foundation\Services;

use Hyde\Hyde;
use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Application;
use Symfony\Component\Yaml\Yaml;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function is_array;

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
        if ($this->hasFile()) {
            $this->boot();
        }
    }

    public function boot(): void
    {
        if ($this->hasFile()) {
            Config::set('site', array_merge(
                Config::get('site', []),
                $this->getYaml()
            ));
        }
    }

    public function hasFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml'))
            || file_exists(Hyde::path('hyde.yaml'));
    }

    protected function getFile(): string
    {
        return file_exists(Hyde::path('hyde.yml'))
            ? Hyde::path('hyde.yml')
            : Hyde::path('hyde.yaml');
    }

    protected function getYaml(): array
    {
        $yaml = Yaml::parse(file_get_contents($this->getFile()));

        return is_array($yaml) ? $yaml : [];
    }
}
