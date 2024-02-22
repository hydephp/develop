<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Illuminate\Contracts\Container\BindingResolutionException;

use function app;
use function array_map;
use function basename;
use function config;
use function get_class;
use function glob;

use Hyde\Console\ConsoleServiceProvider;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Services\AssetService;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;

use function method_exists;

/**
 * @covers \Hyde\Framework\HydeServiceProvider
 * @covers \Hyde\Framework\Concerns\RegistersFileLocations
 * @covers \Hyde\Foundation\Providers\ConfigurationServiceProvider
 * @covers \Hyde\Foundation\Providers\NavigationServiceProvider
 * @covers \Hyde\Foundation\Providers\ViewServiceProvider
 */
class HydeServiceProviderTest extends TestCase
{
    protected HydeServiceProvider $provider;

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = new HydeServiceProvider(app());
    }

    public function testProviderIsConstructed()
    {
        $this->assertInstanceOf(HydeServiceProvider::class, $this->provider);
    }

    public function testProviderHasRegisterMethod()
    {
        $this->assertTrue(method_exists($this->provider, 'register'));
    }

    public function testProviderHasBootMethod()
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));
    }

    public function testProviderRegistersAssetServiceAsSingleton()
    {
        $this->assertTrue($this->app->bound(AssetService::class));
        $this->assertInstanceOf(AssetService::class, $this->app->make(AssetService::class));
        $this->assertSame($this->app->make(AssetService::class), $this->app->make(AssetService::class));
    }

    public function testProviderRegistersBuildTaskServiceAsSingleton()
    {
        $this->assertTrue($this->app->bound(BuildTaskService::class));
        $this->assertInstanceOf(BuildTaskService::class, $this->app->make(BuildTaskService::class));
        $this->assertSame($this->app->make(BuildTaskService::class), $this->app->make(BuildTaskService::class));
    }

    public function testProviderRegistersSourceDirectories()
    {
        BladePage::setSourceDirectory('');
        MarkdownPage::setSourceDirectory('');
        MarkdownPost::setSourceDirectory('');
        DocumentationPage::setSourceDirectory('');

        $this->assertEquals('', BladePage::sourceDirectory());
        $this->assertEquals('', MarkdownPage::sourceDirectory());
        $this->assertEquals('', MarkdownPost::sourceDirectory());
        $this->assertEquals('', DocumentationPage::sourceDirectory());

        $this->provider->register();

        $this->assertEquals('_pages', BladePage::sourceDirectory());
        $this->assertEquals('_pages', MarkdownPage::sourceDirectory());
        $this->assertEquals('_posts', MarkdownPost::sourceDirectory());
        $this->assertEquals('_docs', DocumentationPage::sourceDirectory());
    }

    public function testProviderRegistersOutputDirectories()
    {
        BladePage::setOutputDirectory('foo');
        MarkdownPage::setOutputDirectory('foo');
        MarkdownPost::setOutputDirectory('foo');
        DocumentationPage::setOutputDirectory('foo');

        $this->assertEquals('foo', BladePage::outputDirectory());
        $this->assertEquals('foo', MarkdownPage::outputDirectory());
        $this->assertEquals('foo', MarkdownPost::outputDirectory());
        $this->assertEquals('foo', DocumentationPage::outputDirectory());

        $this->provider->register();

        $this->assertEquals('', BladePage::outputDirectory());
        $this->assertEquals('', MarkdownPage::outputDirectory());
        $this->assertEquals('posts', MarkdownPost::outputDirectory());
        $this->assertEquals('docs', DocumentationPage::outputDirectory());
    }

    public function testCustomSourceRootsAreAppliedToThePageModels()
    {
        $this->assertSame('_pages', BladePage::sourceDirectory());
        $this->assertSame('_pages', MarkdownPage::sourceDirectory());
        $this->assertSame('_posts', MarkdownPost::sourceDirectory());
        $this->assertSame('_docs', DocumentationPage::sourceDirectory());

        config(['hyde.source_root' => 'foo']);

        $this->provider->register();

        $this->assertSame('foo/_pages', BladePage::sourceDirectory());
        $this->assertSame('foo/_pages', MarkdownPage::sourceDirectory());
        $this->assertSame('foo/_posts', MarkdownPost::sourceDirectory());
        $this->assertSame('foo/_docs', DocumentationPage::sourceDirectory());
    }

    public function testSourceRootSetInConfigIsAssigned()
    {
        $this->assertSame('', Hyde::getSourceRoot());
        config(['hyde.source_root' => 'foo']);

        $this->assertSame('', Hyde::getSourceRoot());

        $this->provider->register();
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function testProviderRegistersSiteOutputDirectory()
    {
        $this->assertEquals('_site', Hyde::getOutputDirectory());

        config(['hyde.output_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals('foo', Hyde::getOutputDirectory());
    }

    public function testProviderRegistersMediaDirectory()
    {
        $this->assertEquals('_media', Hyde::getMediaDirectory());

        config(['hyde.media_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals('foo', Hyde::getMediaDirectory());
        $this->assertEquals('foo', Hyde::getMediaOutputDirectory());
    }

    public function testProviderRegistersBladeViewDiscoveryLocationForConfiguredBladeViewPath()
    {
        config(['view.paths' => []]);
        $this->assertEquals([], config('view.paths'));

        $this->provider->register();

        $this->assertEquals([realpath(Hyde::path('_pages'))], config('view.paths'));
    }

    public function testBladeViewLocationsAreOnlyRegisteredOncePerKey()
    {
        config(['view.paths' => []]);
        $this->assertEquals([], config('view.paths'));

        $this->provider->register();
        $this->provider->register();

        $this->assertEquals([realpath(Hyde::path('_pages'))], config('view.paths'));
    }

    public function testProviderRegistersConsoleCommands()
    {
        $commands = array_map(function ($command) {
            return get_class($command);
        }, Artisan::all());

        $glob = glob(Hyde::vendorPath('src/Console/Commands/*.php'));

        $this->assertNotEmpty($glob);

        foreach ($glob as $file) {
            $class = 'Hyde\Console\Commands\\'.basename($file, '.php');

            $this->assertContains($class, $commands);
        }
    }

    public function testProviderRegistersAdditionalModuleServiceProviders()
    {
        $this->provider->register();

        $this->assertArrayHasKey(ConsoleServiceProvider::class, $this->app->getLoadedProviders());
    }

    public function testProviderRegistersAllPageModelSourcePaths()
    {
        // Find all classes in the Hyde\Pages namespace that are not abstract
        $pages = HydeCoreExtension::getPageClasses();

        // Assert we are testing all page models
        $this->assertEquals([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], $pages);

        /** @var \Hyde\Pages\Concerns\HydePage|string $page */
        foreach ($pages as $page) {
            $page::setSourceDirectory('foo');
        }

        $this->provider->register();

        foreach ($pages as $page) {
            $this->assertNotEquals('foo', $page::sourceDirectory(), "Source directory for $page was not set");
        }
    }

    public function testProviderRegistersAllPageModelOutputPaths()
    {
        $pages = HydeCoreExtension::getPageClasses();

        /** @var \Hyde\Pages\Concerns\HydePage|string $page */
        foreach ($pages as $page) {
            $page::setOutputDirectory('foo');
        }

        $this->provider->register();

        foreach ($pages as $page) {
            $this->assertNotEquals('foo', $page::outputDirectory(), "Output directory for $page was not set");
        }
    }

    public function testProviderRegistersSourceDirectoriesUsingOptionsInConfiguration()
    {
        config(['hyde.source_directories' => [
            HtmlPage::class => 'foo',
            BladePage::class => 'foo',
            MarkdownPage::class => 'foo',
            MarkdownPost::class => 'foo',
            DocumentationPage::class => 'foo',
        ]]);

        $this->provider->register();

        $this->assertEquals('foo', HtmlPage::sourceDirectory());
        $this->assertEquals('foo', BladePage::sourceDirectory());
        $this->assertEquals('foo', MarkdownPage::sourceDirectory());
        $this->assertEquals('foo', MarkdownPost::sourceDirectory());
        $this->assertEquals('foo', DocumentationPage::sourceDirectory());
    }

    public function testSourceDirectoriesCanBeSetUsingKebabCaseClassNames()
    {
        config(['hyde.source_directories' => [
            'html-page' => 'foo',
            'blade-page' => 'foo',
            'markdown-page' => 'foo',
            'markdown-post' => 'foo',
            'documentation-page' => 'foo',
        ]]);

        $this->provider->register();

        $this->assertEquals('foo', HtmlPage::sourceDirectory());
        $this->assertEquals('foo', BladePage::sourceDirectory());
        $this->assertEquals('foo', MarkdownPage::sourceDirectory());
        $this->assertEquals('foo', MarkdownPost::sourceDirectory());
        $this->assertEquals('foo', DocumentationPage::sourceDirectory());
    }

    public function testProviderRegistersOutputDirectoriesUsingOptionsInConfiguration()
    {
        config(['hyde.output_directories' => [
            HtmlPage::class => 'foo',
            BladePage::class => 'foo',
            MarkdownPage::class => 'foo',
            MarkdownPost::class => 'foo',
            DocumentationPage::class => 'foo',
        ]]);

        $this->provider->register();

        $this->assertEquals('foo', HtmlPage::outputDirectory());
        $this->assertEquals('foo', BladePage::outputDirectory());
        $this->assertEquals('foo', MarkdownPage::outputDirectory());
        $this->assertEquals('foo', MarkdownPost::outputDirectory());
        $this->assertEquals('foo', DocumentationPage::outputDirectory());
    }

    public function testOutputDirectoriesCanBeSetUsingKebabCaseClassNames()
    {
        config(['hyde.output_directories' => [
            'html-page' => 'foo',
            'blade-page' => 'foo',
            'markdown-page' => 'foo',
            'markdown-post' => 'foo',
            'documentation-page' => 'foo',
        ]]);

        $this->provider->register();

        $this->assertEquals('foo', HtmlPage::outputDirectory());
        $this->assertEquals('foo', BladePage::outputDirectory());
        $this->assertEquals('foo', MarkdownPage::outputDirectory());
        $this->assertEquals('foo', MarkdownPost::outputDirectory());
        $this->assertEquals('foo', DocumentationPage::outputDirectory());
    }

    public function testCannotMainNavigationMenuFromContainerBeforeKernelIsBooted()
    {
        $this->expectException(BindingResolutionException::class);

        app('navigation.main');
    }

    public function testCannotDocumentationSidebarFromContainerBeforeKernelIsBooted()
    {
        $this->expectException(BindingResolutionException::class);

        app('navigation.sidebar');
    }

    public function testCanGetMainNavigationMenuFromContainer()
    {
        Hyde::boot();

        $this->assertInstanceOf(MainNavigationMenu::class, app('navigation.main'));
    }

    public function testCanGetDocumentationSidebarFromContainer()
    {
        Hyde::boot();

        $this->assertInstanceOf(DocumentationSidebar::class, app('navigation.sidebar'));
    }

    public function testCanGetMainNavigationMenuFromContainerUsingShorthand()
    {
        Hyde::boot();

        $this->assertSame(app('navigation.main'), MainNavigationMenu::get());
    }

    public function testCanGetDocumentationSidebarFromContainerUsingShorthand()
    {
        Hyde::boot();

        $this->assertSame(app('navigation.sidebar'), DocumentationSidebar::get());
    }
}
