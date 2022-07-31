<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * This test class runs high-level tests on the HydeKernel class,
 * as most of the logic actually resides in linked service classes.
 *
 * @covers \Hyde\Framework\HydeKernel
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

}
