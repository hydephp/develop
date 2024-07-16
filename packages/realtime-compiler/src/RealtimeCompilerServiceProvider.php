<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Illuminate\Support\ServiceProvider;
use Hyde\RealtimeCompiler\Http\VirtualRouteController;

class RealtimeCompilerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RealtimeCompiler::class);
    }

    public function boot(): void
    {
        $this->app->make(RealtimeCompiler::class)->registerVirtualRoute('/ping', [VirtualRouteController::class, 'ping']);
    }
}
