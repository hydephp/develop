<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Site;
use Hyde\Framework\Features\DataCollections\DataCollectionServiceProvider;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Artisan;
use function app;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function basename;
use function config;
use function get_class;
use function get_declared_classes;
use function glob;
use function method_exists;
use function str_starts_with;

/**
 * @covers \Hyde\Framework\HydeServiceProvider
 * @covers \Hyde\Framework\Concerns\RegistersFileLocations
 */
class HydeServiceProviderTest extends TestCase
{
    protected HydeServiceProvider $provider;

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = new HydeServiceProvider(app());
    }

    public function test_provider_is_constructed()
    {
        $this->assertInstanceOf(HydeServiceProvider::class, $this->provider);
    }

    public function test_provider_has_register_method()
    {
        $this->assertTrue(method_exists($this->provider, 'register'));
    }

    public function test_provider_has_boot_method()
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));
    }

    public function test_provider_applies_yaml_configuration_when_present()
    {
        $this->assertEquals('HydePHP', config('site.name'));
        $this->file('hyde.yml', 'name: Foo');
        $this->provider->register();
        $this->assertEquals('Foo', config('site.name'));
    }

    public function test_provider_registers_asset_service_contract()
    {
        $this->assertTrue($this->app->bound(AssetService::class));
        $this->assertInstanceOf(AssetService::class, $this->app->make(AssetService::class));
        $this->assertInstanceOf(AssetService::class, $this->app->make(AssetService::class));
    }

    public function test_provider_registers_source_directories()
    {
        BladePage::$sourceDirectory = '';
        MarkdownPage::$sourceDirectory = '';
        MarkdownPost::$sourceDirectory = '';
        DocumentationPage::$sourceDirectory = '';

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

    public function test_provider_registers_output_directories()
    {
        BladePage::$outputDirectory = 'foo';
        MarkdownPage::$outputDirectory = 'foo';
        MarkdownPost::$outputDirectory = 'foo';
        DocumentationPage::$outputDirectory = 'foo';

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

    public function test_custom_source_roots_are_applied_to_the_page_models()
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

    public function test_source_root_set_in_config_is_assigned()
    {
        $this->assertSame('', Hyde::getSourceRoot());
        config(['hyde.source_root' => 'foo']);

        $this->assertSame('', Hyde::getSourceRoot());

        $this->provider->register();
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function test_provider_registers_configured_documentation_output_directory()
    {
        $this->assertEquals('docs', DocumentationPage::outputDirectory());

        config(['docs.output_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals('foo', DocumentationPage::outputDirectory());
    }

    public function test_provider_registers_site_output_directory()
    {
        $this->assertEquals('_site', Site::$outputPath);

        config(['site.output_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals('foo', Site::$outputPath);
    }

    public function test_provider_registers_blade_view_discovery_location_for_configured_blade_view_path()
    {
        config(['view.paths' => []]);
        $this->assertEquals([], config('view.paths'));

        $this->provider->register();

        $this->assertEquals([Hyde::path('_pages')], config('view.paths'));
    }

    public function test_blade_view_locations_are_only_registered_once_per_key()
    {
        config(['view.paths' => []]);
        $this->assertEquals([], config('view.paths'));

        $this->provider->register();
        $this->provider->register();

        $this->assertEquals([Hyde::path('_pages')], config('view.paths'));
    }

    public function test_provider_registers_console_commands()
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

    public function test_provider_registers_additional_module_service_providers()
    {
        $this->provider->register();

        $this->assertArrayHasKey(DataCollectionServiceProvider::class, $this->app->getLoadedProviders());
    }

    public function test_provider_registers_all_page_model_source_paths()
    {
        // Find all classes in the Hyde\Pages namespace that are not abstract
        $pages = array_values(array_filter(get_declared_classes(), function ($class) {
            return str_starts_with($class, 'Hyde\Pages') && !str_starts_with($class, 'Hyde\Pages\Concerns');
        }));

        // Assert we are testing all page models
        $this->assertEquals([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], $pages);

        /** @var \Hyde\Pages\Concerns\HydePage $page */
        foreach ($pages as $page) {
            $page::$sourceDirectory = 'foo';
        }

        $this->provider->register();

        foreach ($pages as $page) {
            $this->assertNotEquals('foo', $page::$sourceDirectory, "Source directory for $page was not set");
        }
    }

    public function test_provider_registers_all_page_model_output_paths()
    {
        // Find all classes in the Hyde\Pages namespace that are not abstract
        $pages = array_values(array_filter(get_declared_classes(), function ($class) {
            return str_starts_with($class, 'Hyde\Pages') && !str_starts_with($class, 'Hyde\Pages\Concerns');
        }));

        // Assert we are testing all page models
        $this->assertEquals([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], $pages);

        /** @var \Hyde\Pages\Concerns\HydePage $page */
        foreach ($pages as $page) {
            $page::$outputDirectory = 'foo';
        }

        $this->provider->register();

        foreach ($pages as $page) {
            $this->assertNotEquals('foo', $page::$outputDirectory, "Output directory for $page was not set");
        }
    }
}
