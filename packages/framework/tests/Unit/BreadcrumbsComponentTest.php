<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\Factory;
use Mockery;

/**
 * @covers \Hyde\Framework\Views\Components\BreadcrumbsComponent
 */
class BreadcrumbsComponentTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        Render::clearResolvedInstance(\Hyde\Support\Models\Render::class);

        app()->forgetInstance(Factory::class);
    }

    public function testCanConstruct()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        $this->assertInstanceOf(BreadcrumbsComponent::class, new BreadcrumbsComponent());
    }

    public function testCanRender()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        app()->instance(ViewFactory::class, Mockery::mock(Factory::class, function ($mock) {
            $mock->shouldReceive('make')->once()->andReturn($mock);
        }));

        $this->assertInstanceOf(Factory::class, (new BreadcrumbsComponent())->render());
    }
}
