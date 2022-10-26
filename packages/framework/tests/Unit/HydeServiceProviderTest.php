<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\DataCollections\DataCollectionServiceProvider;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Support\Site;
use Hyde\Framework\Services\AssetService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;

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

        foreach (glob(Hyde::vendorPath('src/Commands/*.php')) as $file) {
            $class = 'Hyde\Framework\Commands\\'.basename($file, '.php');

            $this->assertContains($class, $commands);
        }
    }

    public function test_provider_registers_additional_module_service_providers()
    {
        $this->provider->register();

        $this->assertArrayHasKey(DataCollectionServiceProvider::class, $this->app->getLoadedProviders());
    }
}
