<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

use function file_exists;
use function file_get_contents;

/**
 * @internal Contains shared logic for loading the YAML configuration file.
 */
class YamlConfigurationRepository
{
    protected bool $booted = false;
    protected array $data;
    protected string|false $file;

    /** @return array<string, scalar|array> */
    public function getData(): array
    {
        $this->bootIfNotBooted();

        return $this->data;
    }

    public function hasYamlConfigFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml'))
            || file_exists(Hyde::path('hyde.yaml'));
    }

    protected function bootIfNotBooted(): void
    {
        if (! $this->booted) {
            $this->boot();
        }
    }

    protected function boot(): void
    {
        $this->data = $this->parseYamlFile();
    }

    protected function parseYamlFile(): array
    {
        return Arr::undot((array) Yaml::parse(file_get_contents($this->getFile())));
    }

    protected function getFile(): string
    {
        return file_exists(Hyde::path('hyde.yml'))
            ? Hyde::path('hyde.yml')
            : Hyde::path('hyde.yaml');
    }
}
