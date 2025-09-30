<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Illuminate\Support\ServiceProvider;
use Hyde\RealtimeCompiler\Http\DashboardController;
use Hyde\RealtimeCompiler\Http\LiveEditController;
use Hyde\RealtimeCompiler\Http\VirtualRouteController;
use Hyde\RealtimeCompiler\Console\Commands\HerdInstallCommand;
use Hyde\RealtimeCompiler\Console\Commands\ServeCommand;

class RealtimeCompilerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RealtimeCompiler::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                HerdInstallCommand::class,
                ServeCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $router = $this->app->make(RealtimeCompiler::class);

        $router->registerVirtualRoute('/ping', [VirtualRouteController::class, 'ping']);

        if (DashboardController::enabled()) {
            $router->registerVirtualRoute('/dashboard', [VirtualRouteController::class, 'dashboard']);
        }

        if (LiveEditController::enabled()) {
            $router->registerVirtualRoute('/_hyde/live-edit', [VirtualRouteController::class, 'liveEdit']);
        }
    }
}
