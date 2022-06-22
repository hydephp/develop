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
        // The old file was removed in hyde/framework v0.40.x

        // To preserve backwards compatibility, we will continue to load
        // the old bootstrap file for several minor versions.

        // Temporarily add compatability for old bootstrap file location making the transition easier
        $this->laravel = require_once file_exists(BASE_PATH.'/app/bootstrap.php') ? BASE_PATH.'/app/bootstrap.php' : BASE_PATH.'/bootstrap/app.php';
    }

    protected function bootApplication(): void
    {
        if (! isset($this->laravel)) {
            $this->createApplication();
        }

        $this->laravel->make(Kernel::class)->bootstrap();
    }
}
