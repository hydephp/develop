<?php

declare(strict_types=1);

namespace Hyde\Framework;

use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Concerns\RegistersFileLocations;
use Hyde\Framework\Services\AssetService;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\ServiceProvider;

/**
 * Register and bootstrap core Hyde application services.
 */
class HydeServiceProvider extends ServiceProvider
{
    use RegistersFileLocations;

    protected HydeKernel $kernel;

    public function register(): void
    {
        $this->kernel = HydeKernel::getInstance();

        $this->app->singleton(AssetService::class, AssetService::class);

        $this->kernel->setSourceRoot(config('hyde.source_root', ''));

        $this->registerSourceDirectories([
            HtmlPage::class => $this->getSourceDirectoryConfiguration(HtmlPage::class, '_pages'),
            BladePage::class => $this->getSourceDirectoryConfiguration(BladePage::class, '_pages'),
            MarkdownPage::class => $this->getSourceDirectoryConfiguration(MarkdownPage::class, '_pages'),
            MarkdownPost::class => $this->getSourceDirectoryConfiguration(MarkdownPost::class, '_posts'),
            DocumentationPage::class => $this->getSourceDirectoryConfiguration(DocumentationPage::class, '_docs'),
        ]);

        $this->registerOutputDirectories([
            HtmlPage::class => $this->getOutputDirectoryConfiguration(HtmlPage::class, ''),
            BladePage::class => $this->getOutputDirectoryConfiguration(BladePage::class, ''),
            MarkdownPage::class => $this->getOutputDirectoryConfiguration(MarkdownPage::class, ''),
            MarkdownPost::class => $this->getOutputDirectoryConfiguration(MarkdownPost::class, 'posts'),
            DocumentationPage::class => $this->getOutputDirectoryConfiguration(DocumentationPage::class, 'docs'),
        ]);

        $this->storeCompiledSiteIn(config('hyde.output_directory', '_site'));

        $this->useMediaDirectory(config('hyde.media_directory', '_media'));

        $this->discoverBladeViewsIn(BladePage::sourceDirectory());
    }

    public function boot(): void
    {
        $this->kernel->readyToBoot();
    }
}
