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
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = parent::getConfigurationFiles($app);

        $files['app'] = $app->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config.php';

        return $files;
    }
}
