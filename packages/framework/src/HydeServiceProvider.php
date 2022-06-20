<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\RegistersDefaultDirectories;
use Hyde\Framework\Contracts\AssetServiceContract;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\MarkdownPage;
use Hyde\Framework\Models\MarkdownPost;
use Hyde\Framework\Services\AssetService;
use Illuminate\Support\ServiceProvider;

/**
 * Register and bootstrap Hyde application services.
 */
class HydeServiceProvider extends ServiceProvider
{
    use RegistersDefaultDirectories;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        /**
         * @deprecated
         */
        $this->app->bind(
            'hyde.version',
            function () {
                return InstalledVersions::getPrettyVersion('hyde/hyde') ?: 'unreleased';
            }
        );

        /**
         * @deprecated
         */
        $this->app->bind(
            'framework.version',
            function () {
                return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
            }
        );

        $this->app->singleton(AssetServiceContract::class, AssetService::class);

        $this->registerDefaultDirectories([
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);

        $this->discoverBladeViewsIn('_pages');

        $this->storeCompiledSiteIn(config(
            'hyde.site_output_path',
            Hyde::path('_site')
        ));

        $this->commands([
            Commands\HydePublishHomepageCommand::class,
            Commands\HydeUpdateConfigsCommand::class,
            Commands\HydePublishViewsCommand::class,
            Commands\HydeRebuildStaticSiteCommand::class,
            Commands\HydeBuildStaticSiteCommand::class,
            Commands\HydeBuildSitemapCommand::class,
            Commands\HydeBuildRssFeedCommand::class,
            Commands\HydeBuildSearchCommand::class,
            Commands\HydeMakePostCommand::class,
            Commands\HydeMakePageCommand::class,
            Commands\HydeValidateCommand::class,
            Commands\HydeInstallCommand::class,
            Commands\HydeDebugCommand::class,
            Commands\HydeServeCommand::class,

            Commands\HydePackageDiscoverCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde');

        $this->publishes([
            __DIR__.'/../config' => config_path(),
        ], 'configs');

        $this->publishes([
            __DIR__.'/../resources/views/layouts' => resource_path('views/vendor/hyde/layouts'),
        ], 'hyde-layouts');

        $this->publishes([
            __DIR__.'/../resources/views/components' => resource_path('views/vendor/hyde/components'),
        ], 'hyde-components');

        $this->publishes([
            __DIR__.'/../_pages/404.blade.php' => resource_path('views/pages/404.blade.php'),
        ], 'hyde-page-404');
    }

    /**
     * If you are loading Blade views from a different directory,
     * you need to add the path to the view.php config. This is
     * here done automatically when registering this provider.
     */
    protected function discoverBladeViewsIn(string $directory): void
    {
        config(['view.paths' => array_merge(
            config('view.paths', []),
            [base_path($directory)]
        )]);
    }

    /**
     * The absolute path to the directory when the compiled site is stored.
     */
    protected function storeCompiledSiteIn(string $directory): void
    {
        StaticPageBuilder::$outputPath = $directory;
    }
}
