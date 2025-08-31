<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use TypeError;
use Hyde\Pages\MarkdownPage;
use Illuminate\View\Factory;
use InvalidArgumentException;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Illuminate\Support\Facades\View;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\RenderData::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Facades\Render::class)]
class RenderHelperTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        Render::swap(new RenderData());
        View::swap(Mockery::mock(Factory::class)->makePartial());
    }

    public function testSetAndGetPage()
    {
        $this->assertNull(Render::getPage());

        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page, Render::getPage());
    }

    public function testSetPageSharesDataToViewAutomatically()
    {
        $this->assertNull(View::shared('page'));

        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page, View::shared('page'));
    }

    public function testGetRoute()
    {
        $this->assertNull(Render::getRoute());

        Render::setPage($page = new MarkdownPage());
        $this->assertEquals($page->getRoute(), Render::getRoute());
    }

    public function testGetRouteKey()
    {
        $this->assertNull(Render::getRouteKey());

        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page->getRouteKey(), Render::getRouteKey());
    }

    public function testShareToView()
    {
        $this->assertNull(View::shared('page'));
        $this->assertNull(View::shared('route'));
        $this->assertNull(View::shared('routeKey'));

        Render::setPage($page = new MarkdownPage());

        $this->assertSame($page, View::shared('page'));
        $this->assertEquals($page->getRoute(), View::shared('route'));
        $this->assertSame($page->getRouteKey(), View::shared('routeKey'));
    }

    public function testShareRouteKey()
    {
        $this->assertNull(Render::getRouteKey());

        Render::share('routeKey', 'foo');

        $this->assertSame('foo', Render::getRouteKey());
    }

    public function testShareRoute()
    {
        $this->assertNull(Render::getRoute());

        $route = new Route(new MarkdownPage());
        Render::share('route', $route);

        $this->assertSame($route, Render::getRoute());
    }

    public function testSharePage()
    {
        $this->assertNull(Render::getPage());

        $page = new MarkdownPage();
        Render::share('page', $page);

        $this->assertSame($page, Render::getPage());
    }

    public function testShareInvalidProperty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'foo' does not exist on Hyde\Support\Models\Render");

        Render::share('foo', 'bar');
    }

    public function testShareInvalidType()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Hyde\Support\Models\RenderData::share(): Argument #2 ($value) must be of type Hyde\Pages\Concerns\HydePage|Hyde\Support\Models\Route|string, array given');

        Render::share('route', ['foo']);
    }

    public function testShareInvalidTypeForProperty()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot assign string to property Hyde\Support\Models\RenderData::$route of type Hyde\Support\Models\Route');

        Render::share('route', 'bar');
    }

    public function testShareCascadesDataToView()
    {
        $this->assertNull(View::shared('routeKey'));

        Render::share('routeKey', 'bar');
        $this->assertSame('bar', View::shared('routeKey'));
    }

    public function testClearData()
    {
        $render = new RenderData();
        $render->setPage(new MarkdownPage());
        $this->assertNotNull($render->getPage());

        $render->clearData();
        $this->assertNull($render->getPage());
    }

    public function testClearDataOnFacade()
    {
        Render::setPage(new MarkdownPage());
        $this->assertNotNull(Render::getPage());

        Render::clearData();
        $this->assertNull(Render::getPage());
    }

    public function testClearDataCascadesToClearItsViewData()
    {
        Render::setPage(new MarkdownPage());

        $this->assertNotNull(View::shared('page'));
        $this->assertNotNull(View::shared('route'));
        $this->assertNotNull(View::shared('routeKey'));

        Render::clearData();
        $this->assertNull(View::shared('page'));
        $this->assertNull(View::shared('route'));
        $this->assertNull(View::shared('routeKey'));
    }

    public function testClearDataDoesNotClearOtherViewData()
    {
        View::share('foo', 'bar');
        $this->assertNotNull(View::shared('foo'));

        Render::clearData();
        $this->assertNotNull(View::shared('foo'));
    }

    public function testClearDataDoesNotClearRenderInstanceFromViewData()
    {
        Render::shareToView();
        $this->assertNotNull(View::shared('render'));

        Render::clearData();
        $this->assertNotNull(View::shared('render'));
    }

    public function testToArray()
    {
        $render = Render::getFacadeRoot();
        $this->assertSame([
            'render' => $render,
            'page' => null,
            'route' => null,
            'routeKey' => null,
        ], $render->toArray());

        Render::setPage($page = new MarkdownPage());
        $this->assertEquals([
            'render' => $render,
            'page' => $page,
            'route' => $page->getRoute(),
            'routeKey' => $page->getRouteKey(),
        ], $render->toArray());
    }
}
