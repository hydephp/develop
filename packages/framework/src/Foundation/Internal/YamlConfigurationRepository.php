<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Hyde;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

use function file_exists;
use function file_get_contents;
use function array_key_first;

/**
 * @internal Contains shared logic for loading and parsing the YAML configuration file.
 *
 * @see LoadYamlEnvironmentVariables Which uses this repository to inject environment variables from the YAML configuration file.
 * @see LoadYamlConfiguration Which uses this repository to merge the YAML configuration data with the existing configuration.
 */
class YamlConfigurationRepository
{
    protected false|string $file;
    protected array $data;

    public function __construct()
    {
        $this->file = $this->getFilePath();

        if ($this->hasYamlConfigFile()) {
            $data = $this->parseYamlFile();

            if (! self::configurationContainsNamespaces($data)) {
                $data = ['hyde' => $data];
            }

            $this->data = $data;
        }
    }

    /** @return array<string, array<string, null|scalar|array>> */
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
            file_exists(Hyde::path('hyde.yml')) => Hyde::path('hyde.yml'),
            file_exists(Hyde::path('hyde.yaml')) => Hyde::path('hyde.yaml'),
            default => false,
        };
    }

    protected static function configurationContainsNamespaces(array $config): bool
    {
        return array_key_first($config) === 'hyde';
    }
}
