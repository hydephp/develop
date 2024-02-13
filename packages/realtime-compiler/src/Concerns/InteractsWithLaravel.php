<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * Provides methods to interact with the Laravel (Hyde) application instance.
 * This is needed to bootstrap core Hyde services, for example page compilers.
 *
 * The application is lazy-loaded and only booted when necessary as it takes ~80ms.
 * This is so routes that don't need the application don't suffer the performance hit.
 */
trait InteractsWithLaravel
{
    protected Application $laravel;

    protected function createApplication(): void
    {
        $this->laravel = require getenv('HYDE_BOOTSTRAP_PATH') ?: (BASE_PATH.'/app/bootstrap.php');
    }

    protected function bootApplication(): void
    {
        if (! isset($this->laravel)) {
            $this->createApplication();
        }

        $this->laravel->make(Kernel::class)->bootstrap();
    }
}
