<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Hyde;

/**
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationServiceTest
 */
class YamlConfigurationService
{
    public function boot(): void
    {
        // TODO: Merge configuration files.
    }

    public function hasFile(): bool
    {
        return file_exists(Hyde::path('hyde.yml')) || file_exists(Hyde::path('hyde.yaml'));
    }
}
