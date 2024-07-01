<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Hyde\Foundation\Application;

/**
 * @internal Inject environment variables parsed from the YAML configuration file.
 */
class LoadYamlConfigurationEnvironmentVariables
{
    /**
     * Performs a core task that needs to be performed on
     * early stages of the framework.
     */
    public function bootstrap(Application $app): void
    {
        //
    }
}
