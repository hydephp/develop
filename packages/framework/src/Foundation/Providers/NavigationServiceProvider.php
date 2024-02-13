<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Features\Navigation\NavigationManager;
use Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NavigationManager::class, function () {
            return new NavigationManager();
        });

        $this->app->alias(NavigationManager::class, 'navigation');

        $this->app->make(HydeKernel::class)->booted(function () {
            $this->app->make(NavigationManager::class)->registerMenu('main', GeneratesMainNavigationMenu::handle());
        });
    }
}
