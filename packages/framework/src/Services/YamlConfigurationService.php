<?php

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
        $yaml = Yaml::parse(file_get_contents(static::getFile()));
        Config::set('site', array_merge(
            Config::get('site', []),
            $yaml
        ));
    }

    public static function hasFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml')) || file_exists(Hyde::path('hyde.yaml'));
    }

    protected static function getFile(): string
    {
        return file_exists(Hyde::path('hyde.yml'))
            ? Hyde::path('hyde.yml')
            : Hyde::path('hyde.yaml');
    }
}
