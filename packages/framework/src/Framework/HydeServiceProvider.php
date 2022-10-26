<?php

declare(strict_types=1);

namespace Hyde\Framework;

use Hyde\Console\HydeConsoleServiceProvider;
use Hyde\DataCollections\DataCollectionServiceProvider;
use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Concerns\RegistersFileLocations;
use Hyde\Framework\Services\AssetService;
use Hyde\Framework\Services\YamlConfigurationService;
use Hyde\Framework\Views\Components\LinkComponent;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
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
     * Register module service providers.
     */
    protected function registerModuleServiceProviders(): void
    {
        $this->app->register(HydeConsoleServiceProvider::class);
        $this->app->register(DataCollectionServiceProvider::class);
    }
}
