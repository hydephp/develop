<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make(HydeKernel::class)->booted(function (): void {
            $this->app->singleton('navigation.main', function (): MainNavigationMenu {
                return NavigationMenuGenerator::handle(MainNavigationMenu::class);
            });

            // When documentation versioning is enabled, this holds the default version's sidebar.
            $this->app->singleton('navigation.sidebar', function (): DocumentationSidebar {
                return NavigationMenuGenerator::handle(DocumentationSidebar::class);
            });

            DocumentationVersions::all()->each(function (DocumentationVersion $version): void {
                $this->app->singleton("navigation.sidebar.$version->name", function () use ($version): DocumentationSidebar {
                    return NavigationMenuGenerator::handle(DocumentationSidebar::class, $version);
                });
            });
        });
    }
}
