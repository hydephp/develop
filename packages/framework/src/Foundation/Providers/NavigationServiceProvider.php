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

            $this->registerDocumentationSidebars();
        });
    }

    /**
     * The documentation sidebar is bound as `navigation.sidebar`. When documentation versioning is
     * enabled, each version gets its own sidebar, and the default service resolves the sidebar
     * belonging to the default version. Sidebars should be resolved through the container
     * binding, or better yet, using {@see DocumentationSidebar::get()}, which selects the
     * sidebar matching the version of the page being rendered.
     */
    protected function registerDocumentationSidebars(): void
    {
        $versions = DocumentationVersions::all();

        if ($versions->isEmpty()) {
            $this->app->singleton('navigation.sidebar', function (): DocumentationSidebar {
                return NavigationMenuGenerator::handle(DocumentationSidebar::class);
            });

            return;
        }

        $versions->each(function (DocumentationVersion $version): void {
            /** @internal The version-specific service names are an implementation detail. */
            $this->app->singleton("navigation.sidebar.$version->name", function () use ($version): DocumentationSidebar {
                return NavigationMenuGenerator::handle(DocumentationSidebar::class, $version);
            });
        });

        // Aliasing the default service means both names resolve the same sidebar instance,
        // instead of generating a second sidebar for the same set of documentation pages.
        $this->app->alias('navigation.sidebar.'.DocumentationVersions::default()->name, 'navigation.sidebar');
    }
}
