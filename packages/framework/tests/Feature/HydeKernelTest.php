<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Composer\InstalledVersions;
use Hyde\Facades\Features;
use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

/**
 * This test class runs high-level tests on the HydeKernel class,
 * as most of the logic actually resides in linked service classes.
 *
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Hyde
 *
 * @see \Hyde\Framework\Testing\Unit\HydeHelperFacadeMakeTitleTest
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
        $this->assertEquals(Features::enabled('foo'), Hyde::hasFeature('foo'));
    }

    public function test_current_page_helper_returns_current_page_name()
    {
        View::share('currentPage', 'foo');
        $this->assertEquals('foo', Hyde::currentPage());
    }

    public function test_current_route_helper_returns_current_route_object()
    {
        $expected = new Route(new MarkdownPage());
        View::share('currentRoute', $expected);
        $this->assertInstanceOf(Route::class, Hyde::currentRoute());
        $this->assertEquals($expected, Hyde::currentRoute());
        $this->assertSame($expected, Hyde::currentRoute());
    }

    public function test_make_title_helper_returns_title_from_page_slug()
    {
        $this->assertEquals('Foo Bar', Hyde::makeTitle('foo-bar'));
    }

    public function test_markdown_helper_converts_markdown_to_html()
    {
        $this->assertEquals(new HtmlString("<p>foo</p>\n"), Hyde::markdown('foo'));
    }

    public function test_format_html_path_helper_formats_path_according_to_config_rules()
    {
        Config::set('site.pretty_urls', false);
        $this->assertEquals('foo.html', Hyde::formatLink('foo.html'));
        $this->assertEquals('index.html', Hyde::formatLink('index.html'));

        Config::set('site.pretty_urls', true);
        $this->assertEquals('foo', Hyde::formatLink('foo.html'));
        $this->assertEquals('/', Hyde::formatLink('index.html'));
    }

    public function test_relative_link_helper_returns_relative_link_to_destination()
    {
        View::share('currentPage', 'bar');
        $this->assertEquals('foo', Hyde::relativeLink('foo'));

        View::share('currentPage', 'foo/bar');
        $this->assertEquals('../foo', Hyde::relativeLink('foo'));
    }

    public function test_image_helper_returns_image_path_for_given_name()
    {
        View::share('currentPage', 'foo');
        $this->assertEquals('media/foo.jpg', Hyde::image('foo.jpg'));
        $this->assertEquals('https://example.com/foo.jpg', Hyde::image('https://example.com/foo.jpg'));

        View::share('currentPage', 'foo/bar');
        $this->assertEquals('../media/foo.jpg', Hyde::image('foo.jpg'));
        $this->assertEquals('https://example.com/foo.jpg', Hyde::image('https://example.com/foo.jpg'));
    }

    public function test_has_site_url_helper_returns_boolean_value_for_when_config_setting_is_set()
    {
        Config::set('site.url', 'https://example.com');
        $this->assertTrue(Hyde::hasSiteUrl());

        Config::set('site.url', null);
        $this->assertFalse(Hyde::hasSiteUrl());
    }

    public function test_url_returns_qualified_url_paths()
    {
        Config::set('site.url', 'https://example.com');
        $this->assertEquals('https://example.com', Hyde::url());
        $this->assertEquals('https://example.com/foo', Hyde::url('foo'));

        Config::set('site.pretty_urls', false);
        $this->assertEquals('https://example.com/foo.html', Hyde::url('foo.html'));
        $this->assertEquals('https://example.com/index.html', Hyde::url('index.html'));

        Config::set('site.pretty_urls', true);
        $this->assertEquals('https://example.com/foo', Hyde::url('foo.html'));
        $this->assertEquals('https://example.com', Hyde::url('index.html'));
    }

    public function test_path_returns_qualified_path_for_given_path()
    {
        $this->assertEquals(Hyde::getBasePath(), Hyde::path());
        $this->assertEquals(Hyde::getBasePath().DIRECTORY_SEPARATOR.'foo', Hyde::path('foo'));
    }

    public function test_vendor_path_returns_qualified_path_for_given_path()
    {
        $this->assertEquals(Hyde::getBasePath().DIRECTORY_SEPARATOR.'vendor/hyde/framework', Hyde::vendorPath());
        $this->assertEquals(Hyde::getBasePath().DIRECTORY_SEPARATOR.'vendor/hyde/framework/foo', Hyde::vendorPath('foo'));
    }

    public function test_copy_helper_copies_file_from_given_path_to_given_path()
    {
        touch('foo');
        $this->assertTrue(Hyde::copy('foo', 'bar'));
        $this->assertFileExists('bar');
        unlink('foo');
        unlink('bar');
    }

    public function test_touch_helper_creates_file_at_given_path()
    {
        $this->assertTrue(Hyde::touch('foo'));
        $this->assertFileExists(Hyde::path('foo'));
        Hyde::unlink('foo');
    }

    public function test_unlink_helper_deletes_file_at_given_path()
    {
        Hyde::touch('foo');
        $this->assertTrue(Hyde::unlink('foo'));
        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function test_fluent_model_source_path_helpers()
    {
        $this->assertEquals(Hyde::path('_posts'), Hyde::getModelSourcePath(MarkdownPost::class));
        $this->assertEquals(Hyde::path('_pages'), Hyde::getModelSourcePath(MarkdownPage::class));
        $this->assertEquals(Hyde::path('_docs'), Hyde::getModelSourcePath(DocumentationPage::class));
        $this->assertEquals(Hyde::path('_pages'), Hyde::getModelSourcePath(BladePage::class));

        $this->assertEquals(Hyde::path('_pages'), Hyde::getBladePagePath());
        $this->assertEquals(Hyde::path('_pages'), Hyde::getMarkdownPagePath());
        $this->assertEquals(Hyde::path('_posts'), Hyde::getMarkdownPostPath());
        $this->assertEquals(Hyde::path('_docs'), Hyde::getDocumentationPagePath());
        $this->assertEquals(Hyde::path('_site'), Hyde::sitePath());
    }

    public function test_path_to_relative_helper_returns_relative_path_for_given_path()
    {
        $this->assertEquals('foo', Hyde::pathToRelative(Hyde::path('foo')));
    }

    public function test_to_array_method()
    {
        $array = Hyde::toArray();

        $this->assertTrue(is_array($array));
        $this->assertCount(5, $array);

        $this->assertArrayHasKey('basePath', $array);
        $this->assertArrayHasKey('features', $array);
        $this->assertArrayHasKey('files', $array);
        $this->assertArrayHasKey('pages', $array);
        $this->assertArrayHasKey('routes', $array);

        $this->assertEquals(Hyde::getBasePath(), $array['basePath']);
        $this->assertEquals(Hyde::features(), $array['features']);
        $this->assertEquals(Hyde::files(), $array['files']);
        $this->assertEquals(Hyde::pages(), $array['pages']);
        $this->assertEquals(Hyde::routes(), $array['routes']);

        $this->assertEquals([
            'basePath' => Hyde::getBasePath(),
            'features' => Hyde::features(),
            'files' => Hyde::files(),
            'pages' => Hyde::pages(),
            'routes' => Hyde::routes(),
        ], Hyde::toArray());
    }

    public function test_version_constant_is_a_valid_semver_string()
    {
        // https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-stringd
        $this->assertMatchesRegularExpression(
            '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/',
            HydeKernel::VERSION
        );
    }

    public function test_version_constant_is_up_to_date()
    {
        $this->assertTrue(version_compare(
            HydeKernel::VERSION, InstalledVersions::getPrettyVersion('hyde/framework')
            ) >= 0);
    }

    public function test_version_method_returns_version_constant()
    {
        $this->assertSame(HydeKernel::VERSION, Hyde::version());
    }

    public function test_can_get_source_root()
    {
        $this->assertEquals('', Hyde::getSourceRoot());
    }

    public function test_can_set_source_root()
    {
        Hyde::setSourceRoot('foo');
        $this->assertEquals('foo', Hyde::getSourceRoot());
    }

    public function get_discovered_page_types_method()
    {
        $this->assertSame([BladePage::class], Hyde::getDiscoveredPageTypes());
    }
}
