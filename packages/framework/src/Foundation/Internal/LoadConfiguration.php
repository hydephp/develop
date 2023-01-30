<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Contracts\Foundation\Application;

/**
 * @internal
 */
class LoadConfiguration extends \Illuminate\Foundation\Bootstrap\LoadConfiguration
{
    /**
     * Get all of the configuration files for the application.
     */
    protected function getConfigurationFiles(Application $app): array
    {
        $files = parent::getConfigurationFiles($app);

        // Inject our custom config file which is stored in `app/config.php`.
        $files['app'] = $app->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config.php';

        return $files;
    }
}
