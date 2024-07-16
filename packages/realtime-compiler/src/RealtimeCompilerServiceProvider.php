<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Illuminate\Support\ServiceProvider;

class RealtimeCompilerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RealtimeCompiler::class, function () {
            return new RealtimeCompiler();
        });
    }
}
