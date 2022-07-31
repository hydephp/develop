<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

/**
 * This test class runs high-level tests on the HydeKernel class,
 * as most of the logic actually resides in linked service classes.
 *
 * @covers \Hyde\Framework\HydeKernel
 *
 * @see \Hyde\Framework\Testing\Unit\HydeHelperFacadeMakeTitleTest
 */
class HydeKernelTest extends TestCase
{
    public function test_kernel_singleton_can_be_accessed_by_service_container()
    {
        $this->assertSame(app(HydeKernelContract::class), app(HydeKernelContract::class));
    }

    public function test_kernel_singleton_can_be_accessed_by_kernel_static_method()
    {
        $this->assertSame(app(HydeKernelContract::class), HydeKernel::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_hyde_facade_method()
    {
        $this->assertSame(app(HydeKernelContract::class), Hyde::getInstance());
    }

    public function test_kernel_singleton_can_be_accessed_by_helper_function()
    {
        $this->assertSame(app(HydeKernelContract::class), hyde());
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
        $this->assertInstanceOf(RouteContract::class, Hyde::currentRoute());
        $this->assertEquals($expected, Hyde::currentRoute());
        $this->assertSame($expected, Hyde::currentRoute());
    }

    public function test_make_title_helper_returns_title_from_page_slug()
    {
        $this->assertEquals('Foo Bar', Hyde::makeTitle('foo-bar'));
    }

    public function test_format_html_path_helper_formats_path_according_to_config_rules()
    {
        Config::set('site.pretty_urls', false);
        $this->assertEquals('foo.html', Hyde::formatHtmlPath('foo.html'));
        $this->assertEquals('index.html', Hyde::formatHtmlPath('index.html'));

        Config::set('site.pretty_urls', true);
        $this->assertEquals('foo', Hyde::formatHtmlPath('foo.html'));
        $this->assertEquals('/', Hyde::formatHtmlPath('index.html'));
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
}
