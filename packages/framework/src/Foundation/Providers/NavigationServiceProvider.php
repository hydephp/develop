<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Facades\Localization;
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
            $this->registerMainNavigationMenu();

            $this->registerDocumentationSidebars();
        });
    }

    /**
     * Register the main navigation menu.
     *
     * A localized site needs one menu per language, as an English page must not list the
     * Swedish routes in its menu. The menus are memoized per language rather than bound
     * under a language-qualified key, as the language is only known once a page is
     * being rendered, which is long after these bindings are registered.
     */
    protected function registerMainNavigationMenu(): void
    {
        $menus = [];

        $this->app->bind('navigation.main', function () use (&$menus): MainNavigationMenu {
            $language = Localization::currentLanguage();

            return $menus[$language ?? ''] ??= NavigationMenuGenerator::handle(MainNavigationMenu::class, null, $language);
        });
    }

    /** Register and alias the version-specific documentation sidebars. */
    protected function registerDocumentationSidebars(): void
    {
        $sidebars = [];

        $make = function (?DocumentationVersion $version) use (&$sidebars): DocumentationSidebar {
            $language = Localization::currentLanguage();

            return $sidebars["{$version?->name}@{$language}"] ??= NavigationMenuGenerator::handle(DocumentationSidebar::class, $version, $language);
        };

        $versions = DocumentationVersions::all();

        if ($versions->isEmpty()) {
            $this->app->bind('navigation.sidebar', function () use ($make): DocumentationSidebar {
                return $make(null);
            });

            return;
        }

        $versions->each(function (DocumentationVersion $version) use ($make): void {
            $this->app->bind("navigation.sidebar.$version->name", function () use ($make, $version): DocumentationSidebar {
                return $make($version);
            });
        });

        $this->app->alias('navigation.sidebar.'.DocumentationVersions::default()->name, 'navigation.sidebar');
    }
}
