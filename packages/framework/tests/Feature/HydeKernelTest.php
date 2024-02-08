<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Throwable;
use Composer\InstalledVersions;
use Hyde\Facades\Features;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Filesystem;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Hyde\Framework\Features\Navigation\NavigationMenu;

/**
 * This test class runs high-level tests on the HydeKernel class,
 * as most of the logic actually resides in linked service classes.
 *
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Hyde
 *
 * @see \Hyde\Framework\Testing\Unit\HydeHelperFacadeMakeTitleTest
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionFeatureTest
 */
class HydeKernelTest extends TestCase
{
    public function test_kernel_singleton_can_be_accessed_by_service_container()
    {
        $this->assertSame(app(HydeKernel::class), app(HydeKernel::class));
    }

    public function test_kernel_singleton_can_be_accessed_by_kernel_static_method()
    {
        $this->assertSame(app(HydeKernel::class), HydeKernel::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_hyde_facade_method()
    {
        $this->assertSame(app(HydeKernel::class), Hyde::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_helper_function()
    {
        $this->assertSame(app(HydeKernel::class), hyde());
    }

    public function test_hyde_facade_version_method_returns_kernel_version()
    {
        $this->assertSame(HydeKernel::version(), Hyde::version());
    }

    public function test_hyde_facade_get_facade_root_method_returns_kernel_singleton()
    {
        $this->assertSame(app(HydeKernel::class), Hyde::getFacadeRoot());
        $this->assertSame(HydeKernel::getInstance(), Hyde::getFacadeRoot());
        $this->assertSame(Hyde::getInstance(), Hyde::getFacadeRoot());
    }

    public function test_features_helper_returns_new_features_instance()
    {
        $this->assertInstanceOf(Features::class, Hyde::features());
    }

    public function test_has_feature_helper_calls_method_on_features_class()
    {
        $this->assertSame(Features::enabled('foo'), Hyde::hasFeature('foo'));
    }

    public function test_has_navigation_helper_returns_site_navigation_instance()
    {
        Hyde::boot();

        $this->assertInstanceOf(NavigationMenu::class, Hyde::navigation());
    }

    public function test_current_page_helper_returns_current_page_name()
    {
        Render::share('routeKey', 'foo');
        $this->assertSame('foo', Hyde::currentRouteKey());
    }

    public function test_current_route_helper_returns_current_route_object()
    {
        $expected = new Route(new MarkdownPage());
        Render::share('route', $expected);
        $this->assertInstanceOf(Route::class, Hyde::currentRoute());
        $this->assertSame($expected, Hyde::currentRoute());
    }

    public function test_current_page_helper_returns_current_page_object()
    {
        $expected = new MarkdownPage();
        Render::share('page', $expected);
        $this->assertInstanceOf(HydePage::class, Hyde::currentPage());
        $this->assertSame($expected, Hyde::currentPage());
    }

    public function test_make_title_helper_returns_title_from_page_slug()
    {
        $this->assertSame('Foo Bar', Hyde::makeTitle('foo-bar'));
    }

    public function test_normalize_newlines_replaces_carriage_returns_with_unix_endings()
    {
        $this->assertSame("foo\nbar\nbaz", Hyde::normalizeNewlines("foo\nbar\r\nbaz"));
    }

    public function test_strip_newlines_helper_removes_all_newlines()
    {
        $this->assertSame('foo bar baz', Hyde::stripNewlines("foo\n bar\r\n baz"));
    }

    public function test_trimSlashes_function_trims_trailing_slashes()
    {
        $tests = ['foo',  '/foo',  'foo/',  '/foo/',  '\foo\\',  '\\/foo/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo', Hyde::trimSlashes($test));
        }

        $tests = ['',  '/',  '\\',  '/\\'];

        foreach ($tests as $test) {
            $this->assertSame('', Hyde::trimSlashes($test));
        }

        $tests = ['foo/bar',  'foo/bar/',  'foo/bar\\',  '\\/foo/bar/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo/bar', Hyde::trimSlashes($test));
        }
    }

    public function test_markdown_helper_converts_markdown_to_html()
    {
        $this->assertEquals(new HtmlString("<p>foo</p>\n"), Hyde::markdown('foo'));
    }

    public function test_markdown_helper_converts_indented_markdown_to_html()
    {
        $this->assertEquals(new HtmlString("<p>foo</p>\n"), Hyde::markdown('    foo', true));
    }

    public function test_format_html_path_helper_formats_path_according_to_config_rules()
    {
        Config::set('hyde.pretty_urls', false);
        $this->assertSame('foo.html', Hyde::formatLink('foo.html'));
        $this->assertSame('index.html', Hyde::formatLink('index.html'));

        Config::set('hyde.pretty_urls', true);
        $this->assertSame('foo', Hyde::formatLink('foo.html'));
        $this->assertSame('/', Hyde::formatLink('index.html'));
    }

    public function test_relative_link_helper_returns_relative_link_to_destination()
    {
        Render::share('routeKey', 'bar');
        $this->assertSame('foo', Hyde::relativeLink('foo'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../foo', Hyde::relativeLink('foo'));
    }

    public function test_media_link_helper_returns_relative_link_to_destination()
    {
        Render::share('routeKey', 'bar');
        $this->assertSame('media/foo', Hyde::mediaLink('foo'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../media/foo', Hyde::mediaLink('foo'));
    }

    public function test_image_helper_returns_image_path_for_given_name()
    {
        Render::share('routeKey', 'foo');
        $this->assertSame('media/foo.jpg', Hyde::asset('foo.jpg'));
        $this->assertSame('https://example.com/foo.jpg', Hyde::asset('https://example.com/foo.jpg'));

        Render::share('routeKey', 'foo/bar');
        $this->assertSame('../media/foo.jpg', Hyde::asset('foo.jpg'));
        $this->assertSame('https://example.com/foo.jpg', Hyde::asset('https://example.com/foo.jpg'));
    }

    public function test_image_helper_trims_media_prefix()
    {
        $this->assertSame('media/foo.jpg', Hyde::asset('media/foo.jpg'));
    }

    public function test_image_helper_supports_custom_media_directories()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertSame('assets/foo.jpg', Hyde::asset('foo.jpg'));
    }

    public function test_has_site_url_helper_returns_boolean_value_for_when_config_setting_is_set()
    {
        Config::set('hyde.url', 'https://example.com');
        $this->assertTrue(Hyde::hasSiteUrl());

        Config::set('hyde.url', null);
        $this->assertFalse(Hyde::hasSiteUrl());
    }

    public function test_url_returns_qualified_url_paths()
    {
        Config::set('hyde.url', 'https://example.com');
        $this->assertSame('https://example.com', Hyde::url());
        $this->assertSame('https://example.com/foo', Hyde::url('foo'));

        Config::set('hyde.pretty_urls', false);
        $this->assertSame('https://example.com/foo.html', Hyde::url('foo.html'));
        $this->assertSame('https://example.com/index.html', Hyde::url('index.html'));

        Config::set('hyde.pretty_urls', true);
        $this->assertSame('https://example.com/foo', Hyde::url('foo.html'));
        $this->assertSame('https://example.com', Hyde::url('index.html'));
    }

    public function test_filesystem_helper_returns_the_kernel_filesystem_instance()
    {
        $this->assertInstanceOf(Filesystem::class, Hyde::filesystem());
    }

    public function test_path_returns_qualified_path_for_given_path()
    {
        $this->assertSame(Hyde::getBasePath(), Hyde::path());
        $this->assertSame(Hyde::getBasePath().'/foo', Hyde::path('foo'));
    }

    public function test_vendor_path_returns_qualified_path_for_given_path()
    {
        $this->assertSame(Hyde::getBasePath().'/vendor/hyde/framework', Hyde::vendorPath());
        $this->assertSame(Hyde::getBasePath().'/vendor/hyde/framework/foo', Hyde::vendorPath('foo'));
    }

    public function test_fluent_model_source_path_helpers()
    {
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());

        $this->assertSame(Hyde::path('_media'), Hyde::mediaPath());
        $this->assertSame(Hyde::path('_pages'), BladePage::path());
        $this->assertSame(Hyde::path('_pages'), MarkdownPage::path());
        $this->assertSame(Hyde::path('_posts'), MarkdownPost::path());
        $this->assertSame(Hyde::path('_docs'), DocumentationPage::path());
        $this->assertSame(Hyde::path('_site'), Hyde::sitePath());
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function test_path_to_relative_helper_returns_relative_path_for_given_path()
    {
        $this->assertSame('foo', Hyde::pathToRelative(Hyde::path('foo')));
    }

    public function test_to_array_method()
    {
        // AssertSame cannot be used as features is reinstantiated on each call
        $this->assertEquals([
            'basePath' => Hyde::getBasePath(),
            'sourceRoot' => Hyde::getSourceRoot(),
            'outputDirectory' => Hyde::getOutputDirectory(),
            'mediaDirectory' => Hyde::getMediaDirectory(),
            'extensions' => Hyde::getRegisteredExtensions(),
            'features' => Hyde::features(),
            'files' => Hyde::files(),
            'pages' => Hyde::pages(),
            'routes' => Hyde::routes(),
        ], Hyde::toArray());
    }

    public function test_json_serialize_method()
    {
        $this->assertEquals(Hyde::kernel()->jsonSerialize(), collect(Hyde::toArray())->toArray());
    }

    public function test_to_json_method()
    {
        $this->assertEquals(Hyde::kernel()->toJson(), json_encode(Hyde::toArray()));
    }

    public function test_version_constant_is_a_valid_semver_string()
    {
        // https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-stringd
        $this->assertMatchesRegularExpression(
            '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/',
            HydeKernel::VERSION
        );
    }

    public function test_version_constant_is_up_to_date_with_composer()
    {
        $version = InstalledVersions::getPrettyVersion('hyde/framework');

        if (str_starts_with($version, 'dev-')) {
            $this->markTestSkipped('Installed version is for development');
        }

        $this->assertSame(HydeKernel::VERSION, $version);
    }

    public function test_version_constant_is_up_to_date_with_git()
    {
        try {
            $version = trim(shell_exec('git describe --abbrev=0 --tags'));
        } catch (Throwable) {
            $this->markTestSkipped('Could not get version from Git');
        }

        if ('v'.HydeKernel::VERSION === $version) {
            $this->assertSame('v'.HydeKernel::VERSION, $version);
        } else {
            $this->markTestSkipped('Version constant does not match Git version!');
        }
    }

    public function test_version_method_returns_version_constant()
    {
        $this->assertSame(HydeKernel::VERSION, Hyde::version());
    }

    public function test_can_get_source_root()
    {
        $this->assertSame('', Hyde::getSourceRoot());
    }

    public function test_can_set_source_root()
    {
        Hyde::setSourceRoot('foo');
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function test_can_set_source_root_to_absolute_project_path()
    {
        Hyde::setSourceRoot(Hyde::path('foo'));
        $this->assertSame('foo', Hyde::getSourceRoot());
    }

    public function test_set_source_root_trims_trailing_slashes()
    {
        Hyde::setSourceRoot('/foo/');
        $this->assertSame('/foo', Hyde::getSourceRoot());
    }

    public function test_can_get_output_directory()
    {
        $this->assertSame('_site', Hyde::getOutputDirectory());
    }

    public function test_can_set_output_directory()
    {
        Hyde::setOutputDirectory('foo');
        $this->assertSame('foo', Hyde::getOutputDirectory());
        $this->assertSame(Hyde::path('foo'), Hyde::sitePath());
    }

    public function test_can_set_output_directory_to_absolute_project_path()
    {
        Hyde::setOutputDirectory(Hyde::path('foo'));
        $this->assertSame('foo', Hyde::getOutputDirectory());
        $this->assertSame(Hyde::path('foo'), Hyde::sitePath());
    }

    public function test_set_output_directory_trims_trailing_slashes()
    {
        Hyde::setOutputDirectory('/foo/');
        $this->assertSame('/foo', Hyde::getOutputDirectory());
    }

    public function test_can_get_media_directory()
    {
        $this->assertSame('_media', Hyde::getMediaDirectory());
    }

    public function test_can_set_media_directory()
    {
        Hyde::setMediaDirectory('foo');
        $this->assertSame('foo', Hyde::getMediaDirectory());
    }

    public function test_set_media_directory_trims_trailing_slashes()
    {
        Hyde::setMediaDirectory('/foo/');
        $this->assertSame('/foo', Hyde::getMediaDirectory());
    }

    public function test_can_get_media_output_directory_name()
    {
        $this->assertSame('media', Hyde::getMediaOutputDirectory());
    }

    public function test_get_media_output_directory_name_uses_trimmed_version_of_media_source_directory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame('foo', Hyde::getMediaOutputDirectory());
    }

    public function test_can_get_site_media_output_directory()
    {
        $this->assertSame(Hyde::path('_site/media'), Hyde::siteMediaPath());
    }

    public function test_get_site_media_output_directory_uses_trimmed_version_of_media_source_directory()
    {
        Hyde::setMediaDirectory('_foo');
        $this->assertSame(Hyde::path('_site/foo'), Hyde::siteMediaPath());
    }

    public function test_get_site_media_output_directory_uses_configured_site_output_directory()
    {
        Hyde::setOutputDirectory(Hyde::path('foo'));
        Hyde::setMediaDirectory('bar');
        $this->assertSame(Hyde::path('foo/bar'), Hyde::siteMediaPath());
    }

    public function test_media_output_directory_can_be_changed_in_configuration()
    {
        $this->assertEquals('_media', Hyde::getMediaDirectory());

        config(['hyde.media_directory' => '_assets']);
        (new HydeServiceProvider($this->app))->register();

        $this->assertEquals('_assets', Hyde::getMediaDirectory());
    }

    public function test_can_access_kernel_fluently_using_the_facade()
    {
        $this->assertInstanceOf(HydeKernel::class, Hyde::kernel());
        $this->assertSame(HydeKernel::getInstance(), Hyde::kernel());
        $this->assertSame(HydeKernel::VERSION, Hyde::kernel()->version());
    }

    public function test_can_register_booting_callback_closure()
    {
        $kernel = new HydeKernel();

        $kernel->booting(function () {
            $this->assertTrue(true);
        });

        $kernel->boot();
    }

    public function test_can_register_booted_callback_closure()
    {
        $kernel = new HydeKernel();

        $kernel->booted(function () {
            $this->assertTrue(true);
        });

        $kernel->boot();
    }

    public function test_can_register_booting_callback_callable()
    {
        $kernel = new HydeKernel();

        $kernel->booting(new CallableClass($this));

        $kernel->boot();
    }

    public function test_can_register_booted_callback_callable()
    {
        $kernel = new HydeKernel();

        $kernel->booted(new CallableClass($this));

        $kernel->boot();
    }

    public function test_booting_callback_receives_kernel_instance()
    {
        $kernel = new HydeKernel();

        $kernel->booting(function ($_kernel) use ($kernel) {
            $this->assertSame($kernel, $_kernel);
        });

        $kernel->boot();
    }

    public function test_booted_callback_receives_kernel_instance()
    {
        $kernel = new HydeKernel();

        $kernel->booted(function ($_kernel) use ($kernel) {
            $this->assertSame($kernel, $_kernel);
        });

        $kernel->boot();
    }

    public function test_can_use_booting_callbacks_to_inject_custom_pages()
    {
        $kernel = HydeKernel::getInstance();

        $page = new InMemoryPage('foo');
        $kernel->booting(function (HydeKernel $kernel) use ($page): void {
            $kernel->pages()->addPage($page);
        });

        $this->assertSame($page, Pages::getPage('foo'));
        $this->assertEquals($page->getRoute(), Routes::getRoute('foo'));
    }

    public function test_is_booted_returns_false_when_not_booted()
    {
        $kernel = new HydeKernel();
        $this->assertFalse($kernel->isBooted());
    }

    public function test_is_booted_returns_true_when_booted()
    {
        $kernel = new HydeKernel();
        $kernel->boot();
        $this->assertTrue($kernel->isBooted());
    }

    public function test_is_booted_method_on_the_facade()
    {
        $this->assertFalse(Hyde::isBooted());
        Hyde::kernel()->boot();
        $this->assertTrue(Hyde::isBooted());
    }
}

class CallableClass
{
    private TestCase $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function __invoke(): void
    {
        $this->test->assertTrue(true);
    }
}
