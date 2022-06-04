<?php

namespace Hyde\RealtimeCompiler\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait InteractsWithLaravel
{
    protected Application $laravel;

    protected function createApplication(): void
    {
        $this->laravel = require_once BASE_PATH.'/bootstrap/app.php';
    }

    protected function bootApplication(): void
    {
        if (!isset($this->laravel)) {
            $this->createApplication();
        }

        $this->laravel->make(Kernel::class)->bootstrap();
    }
}
