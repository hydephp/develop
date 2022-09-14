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
        $this->laravel = require_once BASE_PATH.'/app/bootstrap.php';
    }

    protected function bootApplication(): void
    {
        if (! isset($this->laravel)) {
            $this->createApplication();
        }

        $this->laravel->make(Kernel::class)->bootstrap();
    }
}
