<?php

declare(strict_types=1);

namespace Hyde\Framework;

use Hyde\Console\HydeConsoleServiceProvider;
use Hyde\Facades\Features;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Providers\ConfigurationServiceProvider;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Framework\Concerns\RegistersFileLocations;
use Hyde\Framework\Services\AssetService;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\ServiceProvider;

/**
 * Register and bootstrap Hyde application services.
 */
class HydeServiceProvider extends ServiceProvider
{
    use RegistersFileLocations;

    protected HydeKernel $kernel;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->initializeConfiguration();

        $this->kernel = HydeKernel::getInstance();

        $this->app->singleton(AssetService::class, AssetService::class);

        $this->kernel->setSourceRoot(config('hyde.source_root', ''));

        $this->registerPageModels();

        $this->registerSourceDirectories([
            HtmlPage::class => '_pages',
            BladePage::class => '_pages',
            MarkdownPage::class => '_pages',
            MarkdownPost::class => '_posts',
            DocumentationPage::class => '_docs',
        ]);

        $this->registerOutputDirectories([
            HtmlPage::class => '',
            BladePage::class => '',
            MarkdownPage::class => '',
            MarkdownPost::class => 'posts',
            DocumentationPage::class => config('docs.output_directory', 'docs'),
        ]);

        $this->storeCompiledSiteIn(config('hyde.output_directory', '_site'));

        $this->useMediaDirectory(config('hyde.media_directory', '_media'));

        $this->discoverBladeViewsIn(BladePage::sourceDirectory());

        $this->registerModuleServiceProviders();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->kernel->readyToBoot();
    }

    protected function initializeConfiguration(): void
    {
        $this->app->register(ConfigurationServiceProvider::class)->initialize();
    }

    /**
     * Register the page model classes that Hyde should use.
     */
    protected function registerPageModels(): void
    {
        if (Features::hasHtmlPages()) {
            $this->kernel->registerPageClass(HtmlPage::class);
        }

        if (Features::hasBladePages()) {
            $this->kernel->registerPageClass(BladePage::class);
        }

        if (Features::hasMarkdownPages()) {
            $this->kernel->registerPageClass(MarkdownPage::class);
        }

        if (Features::hasMarkdownPosts()) {
            $this->kernel->registerPageClass(MarkdownPost::class);
        }

        if (Features::hasDocumentationPages()) {
            $this->kernel->registerPageClass(DocumentationPage::class);
        }
    }

    /**
     * Register module service providers.
     */
    protected function registerModuleServiceProviders(): void
    {
        $this->app->register(HydeConsoleServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
    }
}
