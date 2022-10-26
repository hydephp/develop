<?php

declare(strict_types=1);

namespace Hyde\Framework;

use Hyde\DataCollections\DataCollectionServiceProvider;
use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Concerns\RegistersFileLocations;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\AssetService;
use Hyde\Framework\Services\YamlConfigurationService;
use Hyde\Framework\Views\Components\LinkComponent;
use Hyde\Hyde;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Register and bootstrap Hyde application services.
 */
class HydeServiceProvider extends ServiceProvider
{
    use RegistersFileLocations;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->initializeConfiguration();

        $this->app->singleton(AssetService::class, AssetService::class);

        $this->app->singleton(MarkdownConverter::class, function () {
            return new MarkdownConverter();
        });

        $this->registerSourceDirectories([
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);

        $this->registerOutputDirectories([
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => unslash(config('docs.output_directory', 'docs')),
        ]);

        $this->storeCompiledSiteIn(unslash(config('site.output_directory', '_site')));

        $this->discoverBladeViewsIn(BladePage::sourceDirectory());

        $this->registerHydeConsoleCommands();
        $this->registerModuleServiceProviders();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'hyde');

        $this->publishes([
            __DIR__.'/../../config' => config_path(),
        ], 'configs');

        $this->publishes([
            __DIR__.'/../../resources/views/layouts' => resource_path('views/vendor/hyde/layouts'),
        ], 'hyde-layouts');

        $this->publishes([
            __DIR__.'/../../resources/views/components' => resource_path('views/vendor/hyde/components'),
        ], 'hyde-components');

        $this->publishes([
            Hyde::vendorPath('resources/views/pages/404.blade.php') => Hyde::path('_pages/404.blade.php'),
        ], 'hyde-page-404');

        $this->publishes([
            Hyde::vendorPath('resources/views/homepages/welcome.blade.php') => Hyde::path('_pages/index.blade.php'),
        ], 'hyde-welcome-page');

        Blade::component('link', LinkComponent::class);

        HydeKernel::getInstance()->boot();
    }

    protected function initializeConfiguration()
    {
        if (YamlConfigurationService::hasFile()) {
            YamlConfigurationService::boot();
        }
    }

    /**
     * Register the HydeCLI console commands.
     */
    protected function registerHydeConsoleCommands(): void
    {
        $this->commands([
            \Hyde\Console\Commands\PublishHomepageCommand::class,
            \Hyde\Console\Commands\UpdateConfigsCommand::class,
            \Hyde\Console\Commands\PublishViewsCommand::class,
            \Hyde\Console\Commands\RebuildStaticSiteCommand::class,
            \Hyde\Console\Commands\BuildSiteCommand::class,
            \Hyde\Console\Commands\BuildSitemapCommand::class,
            \Hyde\Console\Commands\BuildRssFeedCommand::class,
            \Hyde\Console\Commands\BuildSearchCommand::class,
            \Hyde\Console\Commands\RouteListCommand::class,
            \Hyde\Console\Commands\MakePostCommand::class,
            \Hyde\Console\Commands\MakePageCommand::class,
            \Hyde\Console\Commands\ValidateCommand::class,
            \Hyde\Console\Commands\DebugCommand::class,
            \Hyde\Console\Commands\ServeCommand::class,

            \Hyde\Console\Commands\PackageDiscoverCommand::class,
        ]);
    }

    /**
     * Register module service providers.
     */
    protected function registerModuleServiceProviders(): void
    {
        $this->app->register(DataCollectionServiceProvider::class);
    }
}
