<?php

namespace Hyde\RealtimeCompiler\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * Provides methods to interact with the Laravel (Hyde)
 * application instance. This is needed to bootstrap
 * core Hyde services, for example page compilers.
 */
trait InteractsWithLaravel
{
    protected Application $laravel;

    protected function createApplication(): void
    {
        // The core bootstrapping file was moved in hyde/framework v0.35.x.
        // To preserve backwards compatibility, we will continue to load
        // the old bootstrap file for several minor versions.

        $legacyBootstrapper = sprintf('%s/bootstrap/app.php', BASE_PATH);
        $bootstrapper = sprintf('%s/app/bootstrap.php', BASE_PATH);
        if (file_exists($legacyBootstrapper) && !file_exists($bootstrapper)) {
            trigger_error(
                sprintf(
                    'The "%s" file is deprecated since hyde/framework:v0.35.x Please use "%s" instead.',
                    $legacyBootstrapper,
                    $bootstrapper
                ),
                E_USER_DEPRECATED
            );
            $bootstrapper = $legacyBootstrapper;
        }

        $this->laravel = require_once $bootstrapper;
    }

    protected function bootApplication(): void
    {
        if (!isset($this->laravel)) {
            $this->createApplication();
        }

        $this->laravel->make(Kernel::class)->bootstrap();
    }
}
