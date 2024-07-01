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
    protected string|false $file;
    protected array $data;

    /** @return array<string, scalar|array> */
    public function getData(): array
    {
        $this->bootIfNotBooted();

        return $this->data;
    }

    public function hasYamlConfigFile(): bool
    {
        return $this->getFilePath() !== false;
    }

    protected function bootIfNotBooted(): void
    {
        if (! $this->booted) {
            $this->boot();
        }
    }

    protected function boot(): void
    {
        $this->file = $this->getFilePath();
        $this->data = $this->parseYamlFile();
        $this->booted = true;
    }

    protected function parseYamlFile(): array
    {
        return Arr::undot((array) Yaml::parse(file_get_contents($this->file)));
    }

    protected function getFilePath(): string|false
    {
        if (file_exists(Hyde::path('hyde.yml'))) {
            return Hyde::path('hyde.yml');
        }

        if (file_exists(Hyde::path('hyde.yaml'))) {
            return Hyde::path('hyde.yaml');
        }

        return false;
    }
}
