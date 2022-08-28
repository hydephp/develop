<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;

/**
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationServiceTest
 */
class YamlConfigurationService
{
    public static function boot(): void
    {
        // TODO: Merge configuration files.
    }

    public static function hasFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml')) || file_exists(Hyde::path('hyde.yaml'));
    }
}
