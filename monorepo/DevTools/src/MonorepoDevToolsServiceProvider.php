<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use Illuminate\Support\ServiceProvider;

class MonorepoDevToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->commands([
            MonorepoReleaseCommand::class,
        ]);
    }
}
