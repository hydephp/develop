<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

use function file_get_contents;

/**
 * @internal Contains shared logic for loading the YAML configuration file.
 */
class YamlConfigurationRepository
{
    protected false|string $file;
    protected array $data;

    public function __construct()
    {
        $this->file = $this->getFilePath();

        if ($this->file !== false) {
            $this->data = $this->parseYamlFile();
        }
    }

    /** @return array<string, scalar|array> */
    public function getData(): array
    {
        return $this->data;
    }

    public function hasYamlConfigFile(): bool
    {
        return $this->file !== false;
    }

    protected function parseYamlFile(): array
    {
        return Arr::undot((array) Yaml::parse(file_get_contents($this->file)));
    }

    protected function getFilePath(): string|false
    {
        return match (true) {
            Filesystem::exists('hyde.yml') => Hyde::path('hyde.yml'),
            Filesystem::exists('hyde.yaml') => Hyde::path('hyde.yaml'),
            default => false,
        };
    }
}
