<?php
declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationServiceTest
 */
class YamlConfigurationService
{
    public static function boot(): void
    {
        if (static::hasFile()) {
            Config::set('site', array_merge(
                Config::get('site', []),
                static::getYaml()
            ));
        }
    }

    public static function hasFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml'))
            || file_exists(Hyde::path('hyde.yaml'));
    }

    protected static function getFile(): string
    {
        return file_exists(Hyde::path('hyde.yml'))
            ? Hyde::path('hyde.yml')
            : Hyde::path('hyde.yaml');
    }

    protected static function getYaml(): array
    {
        $yaml = Yaml::parse(file_get_contents(static::getFile()));

        return is_array($yaml) ? $yaml : [];
    }
}
