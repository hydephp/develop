<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler;

use Desilva\Microserve\JsonResponse;
use Illuminate\Support\ServiceProvider;

class RealtimeCompilerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RealtimeCompiler::class, function () {
            return new RealtimeCompiler();
        });
    }

    public function boot(): void
    {
        $this->app->make(RealtimeCompiler::class)->registerVirtualRoute('/ping', new JsonResponse(200, 'OK', [
            'server' => 'Hyde/RealtimeCompiler',
        ]));
    }
}
