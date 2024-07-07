<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Hyde\Foundation\Application;
use Illuminate\Config\Repository;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

use Hyde\Framework\Exceptions\InvalidConfigurationException;
use function array_merge;

/**
 * @internal Bootstrap service that loads the YAML configuration file.
 *
 * @see docs/digging-deeper/customization.md#yaml-configuration
 *
 * It also supports loading multiple configuration namespaces, where a configuration namespace is defined
 * as a firs level entry in the service container configuration repository array, and corresponds
 * one-to-one with a file in the config directory, and a root-level key in the YAML file.
 *
 * The namespace feature by design, requires a top-level configuration entry to be present as 'hyde' in the YAML file.
 * Existing config files will be parsed as normal, but can be migrated by indenting all entries by one level,
 * and adding a top-level 'hyde' key. Then additional namespaces can be added underneath as needed.
 */
class LoadYamlConfiguration
{
    protected YamlConfigurationRepository $yaml;
    protected array $config;

    public function bootstrap(Application $app): void
    {
        $this->yaml = $app->make(YamlConfigurationRepository::class);

        if ($this->yaml->hasYamlConfigFile()) {
            tap($app->make('config'), function (Repository $config): void {
                $this->config = $config->all();
                $this->mergeParsedConfiguration();
            })->set($this->config);
        }
    }

    protected function mergeParsedConfiguration(): void
    {
        foreach ($this->yaml->getData() as $namespace => $data) {
            if ($namespace === 'hyde' && isset($data['authors'])) {
                $data['authors'] = $this->parseAuthors($data['authors']);
            }

            $this->mergeConfiguration($namespace, Arr::undot($data ?: []));
        }
    }

    protected function mergeConfiguration(string $namespace, array $yaml): void
    {
        $this->config[$namespace] = array_merge($this->config[$namespace] ?? [], $yaml);
    }

    /**
     * @param  array<string, array{username?: string, name?: string, website?: string, bio?: string, avatar?: string, socials?: array<string, string>}>  $authors
     * @return array<string, \Hyde\Framework\Features\Blogging\Models\PostAuthor>
     */
    protected function parseAuthors(array $authors): array
    {
        return Arr::mapWithKeys($authors, function (array $author, string $username): array {
            try {
                return [$username => PostAuthor::create($author)];
            } catch (Throwable $exception) {
                $message = $exception->getMessage();

                $message = $this->trimExceptionMessage($message);

                throw new InvalidConfigurationException(
                    $message,
                    'hyde',
                    "authors.$username",
                    $this->yaml->getFilePath(),
                    $this->findConfigLine(file($this->yaml->getFilePath()), $username),
                    // $exception
                );
            }
        });
    }

    private function findConfigLine(array $file, string $username): int
    {
        foreach ($file as $line => $content) {
            if (str_contains($content, "$username:")) {
                return $line + 1;
            }
        }
    }

    private function trimExceptionMessage(string $message): string
    {
        // Trim unnecessary information from the exception message.
        $leftStrips = [
            '__construct(): ',
        ];
        $rightStrips = [
            ' called in ',
        ];
        // Trim the left side of the message.
        foreach ($leftStrips as $strip) {
            $message = Str::after($message, $strip);
        }

        // Trim the right side of the message.
        foreach ($rightStrips as $strip) {
            $message = Str::before($message, $strip);
        }

        return trim($message, ' .,');
    }
}
