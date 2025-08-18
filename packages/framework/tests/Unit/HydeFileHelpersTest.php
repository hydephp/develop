<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Illuminate\View\Factory;
use Hyde\Testing\UnitTestCase;
use Hyde\Foundation\Facades\Routes;
use Hyde\Hyde;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Facades\View;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeKernel::class)]
class HydeFileHelpersTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        self::mockRender();

        View::swap(Mockery::mock(Factory::class)->makePartial());
    }

    public function testCurrentPageReturnsCurrentPageViewProperty()
    {
        Render::share('routeKey', 'foo');
        $this->assertSame('foo', Hyde::currentRouteKey());
    }

    public function testCurrentPageFallsBackToNullStringIfCurrentPageViewPropertyIsNotSet()
    {
        $this->assertNull(Hyde::currentRouteKey());
    }

    public function testCurrentRouteReturnsCurrentRouteViewProperty()
    {
        Render::share('route', Routes::get('index'));
        $this->assertSame(Routes::get('index'), Hyde::currentRoute());
    }

    public function testCurrentRouteFallsBackToNullIfCurrentRouteViewPropertyIsNotSet()
    {
        $this->assertNull(Hyde::currentRoute());
    }
}
