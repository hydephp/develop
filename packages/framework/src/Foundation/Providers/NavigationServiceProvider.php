<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\NavigationManager;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NavigationManager::class, function () {
            return new NavigationManager();
        });

        $this->app->alias(NavigationManager::class, 'navigation');

        // Initially bind these to null, so that the container doesn't try to return them before the kernel has booted.
        $this->app->singleton(MainNavigationMenu::class, fn () => null);
        $this->app->singleton(DocumentationSidebar::class, fn () => null);

        $this->app->make(HydeKernel::class)->booted(function () {
            $this->app->singleton(MainNavigationMenu::class, function () {
                return NavigationMenuGenerator::handle(MainNavigationMenu::class);
            });

            $this->app->singleton(DocumentationSidebar::class, function () {
                return NavigationMenuGenerator::handle(DocumentationSidebar::class);
            });

            $this->app->alias(MainNavigationMenu::class, 'navigation.main');
            $this->app->alias(DocumentationSidebar::class, 'navigation.sidebar');

            $this->app->make(NavigationManager::class)->registerMenu('main', NavigationMenuGenerator::handle(MainNavigationMenu::class));
            $this->app->make(NavigationManager::class)->registerMenu('sidebar', NavigationMenuGenerator::handle(DocumentationSidebar::class));
        });
    }
}
