<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Features\Navigation\NavigationManager;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NavigationManager::class, function ($app) {
            return new NavigationManager();
        });

        $this->app->alias(NavigationManager::class, 'navigation');
    }
}
