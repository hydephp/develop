<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Contracts\AssetServiceContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\DataCollections\DataCollectionServiceProvider;
use Hyde\Framework\Services\AssetService;
use Hyde\Framework\StaticPageBuilder;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;

/**
 * @todo #162 Improve testing for this class.
 *
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

    public function test_provider_registers_asset_service_contract()
    {
        $this->assertTrue($this->app->bound(AssetServiceContract::class));
        $this->assertInstanceOf(AssetServiceContract::class, $this->app->make(AssetServiceContract::class));
        $this->assertInstanceOf(AssetService::class, $this->app->make(AssetServiceContract::class));
    }

    public function test_provider_registers_source_directories()
    {
        BladePage::$sourceDirectory = '';
        MarkdownPage::$sourceDirectory = '';
        MarkdownPost::$sourceDirectory = '';
        DocumentationPage::$sourceDirectory = '';

        $this->assertEquals('', BladePage::getSourceDirectory());
        $this->assertEquals('', MarkdownPage::getSourceDirectory());
        $this->assertEquals('', MarkdownPost::getSourceDirectory());
        $this->assertEquals('', DocumentationPage::getSourceDirectory());

        $this->provider->register();

        $this->assertEquals('_pages', BladePage::getSourceDirectory());
        $this->assertEquals('_pages', MarkdownPage::getSourceDirectory());
        $this->assertEquals('_posts', MarkdownPost::getSourceDirectory());
        $this->assertEquals('_docs', DocumentationPage::getSourceDirectory());
    }

    public function test_provider_registers_output_directories()
    {
        BladePage::$outputDirectory = 'foo';
        MarkdownPage::$outputDirectory = 'foo';
        MarkdownPost::$outputDirectory = 'foo';
        DocumentationPage::$outputDirectory = 'foo';

        $this->assertEquals('foo', BladePage::getOutputDirectory());
        $this->assertEquals('foo', MarkdownPage::getOutputDirectory());
        $this->assertEquals('foo', MarkdownPost::getOutputDirectory());
        $this->assertEquals('foo', DocumentationPage::getOutputDirectory());

        $this->provider->register();

        $this->assertEquals('', BladePage::getOutputDirectory());
        $this->assertEquals('', MarkdownPage::getOutputDirectory());
        $this->assertEquals('posts', MarkdownPost::getOutputDirectory());
        $this->assertEquals('docs', DocumentationPage::getOutputDirectory());
    }

    public function test_provider_registers_configured_documentation_output_directory()
    {
        $this->assertEquals('docs', DocumentationPage::getOutputDirectory());

        config(['docs.output_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals('foo', DocumentationPage::getOutputDirectory());
    }

    public function test_provider_registers_site_output_directory()
    {
        $this->assertEquals(Hyde::path('_site'), StaticPageBuilder::$outputPath);

        config(['hyde.output_directory' => 'foo']);

        $this->provider->register();

        $this->assertEquals(Hyde::path('foo'), StaticPageBuilder::$outputPath);
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
            $class = 'Hyde\Framework\Commands\\'. basename($file, '.php');

            $this->assertContains($class, $commands);
        }
    }

    public function test_provider_registers_additional_module_service_providers()
    {
        $this->provider->register();

        $this->assertArrayHasKey(DataCollectionServiceProvider::class, $this->app->getLoadedProviders());
    }
}
