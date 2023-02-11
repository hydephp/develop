<?php

declare(strict_types=1);

namespace Hyde\Foundation\Services;

use Hyde\Framework\Services\YamlConfigurationService;
use LaravelZero\Framework\Application;

/**
 * @internal
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
        if (YamlConfigurationService::hasFile()) {
            YamlConfigurationService::boot();
        }
    }
}
